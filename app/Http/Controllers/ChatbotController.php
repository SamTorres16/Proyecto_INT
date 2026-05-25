<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\QueryParameters;
use Google\Cloud\Dialogflow\V2\Context;
use Google\Protobuf\Struct;
use Google\Protobuf\Value;
use App\Models\Usuario;
use App\Models\Docente;
use App\Models\ActExtraescolar;
use App\Models\Actividad;
use App\Models\HistorialExtraescolar;
use App\Models\CalificacionParcial;

class ChatbotController extends Controller
{
    /**
     * Procesa el mensaje del usuario y retorna la respuesta de Dialogflow.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message'    => 'required|string|max:500',
            'session_id' => 'required|string',
        ]);

        $projectId   = env('DIALOGFLOW_PROJECT_ID');
        $credentials = base_path(env('DIALOGFLOW_CREDENTIALS'));
        $languageCode = env('DIALOGFLOW_LANGUAGE_CODE', 'es');

        try {
            $sessionsClient = new SessionsClient([
                'credentials' => $credentials,
            ]);

            $session = $sessionsClient->sessionName($projectId, $request->session_id);

            // Preparar el input de texto
            $textInput = new TextInput();
            $textInput->setText($request->message);
            $textInput->setLanguageCode($languageCode);

            $queryInput = new QueryInput();
            $queryInput->setText($textInput);

            // Obtener datos del usuario autenticado para el contexto
            $user = auth()->user();
            $userName = $user ? $user->nombre : 'Visitante';
            $userRole = 'Visitante';
            
            if ($user) {
                $rolesMap = [
                    1 => 'Administrador',
                    2 => 'Estudiante',
                    4 => 'Docente'
                ];
                $userRole = $rolesMap[$user->id_tipo] ?? 'Usuario';
            }

            // Configurar contextos de Dialogflow para que el bot "sepa" quién habla
            $context = new Context();
            $context->setName($sessionsClient->contextName($projectId, $request->session_id, 'user_info'));
            $context->setLifespanCount(2);
            $context->setParameters($this->createStruct([
                'user_name' => $userName,
                'user_role' => $userRole
            ]));

            $queryParams = new QueryParameters();
            $queryParams->setContexts([$context]);

            // Realizar la consulta a Dialogflow
            $response = $sessionsClient->detectIntent($session, $queryInput, [
                'queryParams' => $queryParams
            ]);

            $queryResult = $response->getQueryResult();
            $replyText   = $queryResult->getFulfillmentText();
            $intentName  = $queryResult->getIntent() ? $queryResult->getIntent()->getDisplayName() : 'Default Fallback Intent';

            // --- Lógica de Seguridad y Fulfillment ---
            $intentLower = strtolower($intentName);
            $esAdmin = ($user && $user->id_tipo == 1);
            $esDocente = ($user && $user->id_tipo == 4);
            $esEstudiante = ($user && $user->id_tipo == 2);

            $bloqueado = false;

            // Regla: Solo Admin puede usar intents de admin
            if (str_starts_with($intentLower, 'admin.') && !$esAdmin) {
                $bloqueado = true;
            }

            // Regla: Solo Admin o Docente puede usar intents de docente
            if (str_starts_with($intentLower, 'docente.') && !$esAdmin && !$esDocente) {
                $bloqueado = true;
            }

            // Regla: Solo Admin o Estudiante puede usar intents de estudiante
            if (str_starts_with($intentLower, 'estudiante.') && !$esAdmin && !$esEstudiante) {
                $bloqueado = true;
            }

            if ($bloqueado) {
                $replyText = "Lo siento {$userName}, no tienes permisos para acceder a esta función según tu rol de usuario.";
            } else {
                // Si tiene permisos, procesamos el fulfillment (datos reales)
                $dynamicReply = $this->handleFulfillment($intentName, $user);
                if ($dynamicReply) {
                    $replyText = $dynamicReply;
                }
            }

            $sessionsClient->close();

            return response()->json([
                'success' => true,
                'reply'   => $replyText ?: 'Lo siento, no entendí tu mensaje. ¿Puedes intentarlo de nuevo?',
                'intent'  => $intentName
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'reply'   => 'Ocurrió un error al conectar con el asistente. Intenta más tarde.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Procesa intents específicos consultando la base de datos.
     */
    private function handleFulfillment($intentName, $user)
    {
        if (!$user) return null;

        switch ($intentName) {
            // --- CASOS PARA DOCENTE ---
            case 'docente.mis_actividades':
                $docente = Docente::where('no_empleado', $user->num_control)->first();
                if (!$docente) return "No encontré tu perfil de docente vinculado a tu cuenta.";
                
                $actividades = ActExtraescolar::where('no_empleado', $docente->no_empleado)->get();
                if ($actividades->isEmpty()) return "Actualmente no tienes actividades extraescolares asignadas.";
                
                $lista = $actividades->map(fn($a) => "- {$a->nombre} ({$a->horario})")->implode("\n");
                return "Tienes las siguientes actividades asignadas:\n" . $lista;

            case 'docente.resumen_grupo':
                $docente = Docente::where('no_empleado', $user->num_control)->first();
                $conteo = HistorialExtraescolar::whereIn('id_actividad', function ($query) use ($docente) {
                    $query->select('id_act')->from('act_extraesc')->where('no_empleado', $docente->no_empleado);
                })->count();
                return "Tienes un total de {$conteo} alumnos inscritos en todas tus actividades.";

            // --- CASOS PARA ADMINISTRADOR ---
            case 'admin.estadisticas_generales':
                $estudiantes = Usuario::where('id_tipo', 2)->count();
                $docentes = Docente::count();
                $actividades = ActExtraescolar::count();
                return "Resumen del sistema:\n- Estudiantes: {$estudiantes}\n- Docentes: {$docentes}\n- Actividades: {$actividades}";

            case 'admin.actividades_criticas':
                // Obtenemos actividades donde el número de inscritos es cercano al cupo total
                $actividades = ActExtraescolar::all();
                $criticas = [];

                foreach ($actividades as $act) {
                    $totalCupo = ($act->cupo_masculino ?? 0) + ($act->cupo_femenino ?? 0);
                    $totalInscritos = Usuario::where('actividad_extraescolar', $act->id_act)->count();

                    if ($totalCupo > 0 && $totalInscritos >= $totalCupo) {
                        $criticas[] = "- {$act->nombre} (Lleno: {$totalInscritos}/{$totalCupo})";
                    }
                }

                if (empty($criticas)) return "Todas las actividades tienen cupo disponible.";
                
                return "Las siguientes actividades han alcanzado su cupo máximo:\n" . implode("\n", $criticas);

            // --- CASOS PARA ESTUDIANTE ---
            case 'estudiante.mi_actividad':
                $historial = HistorialExtraescolar::with('actividadExtraescolar')
                    ->where('num_control', $user->num_control)
                    ->where('estado', 'Cursando')
                    ->first();
                
                if (!$historial) return "No apareces inscrito en ninguna actividad extraescolar actualmente.";
                
                $act = $historial->actividadExtraescolar;
                $lugar = $act->lugar ?? 'Por definir';
                return "Estás inscrito en: {$act->nombre}.\nHorario: {$act->horario}\nLugar: {$lugar}";

            case 'estudiante.mis_notas':
                $historial = HistorialExtraescolar::with('parciales')
                    ->where('num_control', $user->num_control)
                    ->orderBy('id_historial', 'desc')
                    ->first();
                
                if (!$historial) return "No tengo registros de tus calificaciones aún.";
                
                $notas = $historial->parciales->map(fn($p) => "- Parcial {$p->num_parcial}: {$p->calificacion}")->implode("\n");
                $final = $historial->calificacion_final ? "\nPromedio Final: {$historial->calificacion_final} ({$historial->estado})" : "";
                
                return "Tus calificaciones actuales son:\n" . ($notas ?: "Sin parciales registrados aún.") . $final;

            default:
                return null;
        }
    }

    /**
     * Helper para crear un Struct de Google Protobuf a partir de un array asociativo simple.
     */
    private function createStruct(array $data)
    {
        $struct = new Struct();
        $fields = [];
        foreach ($data as $key => $value) {
            $val = new Value();
            $val->setStringValue((string)$value);
            $fields[$key] = $val;
        }
        $struct->setFields($fields);
        return $struct;
    }
}
