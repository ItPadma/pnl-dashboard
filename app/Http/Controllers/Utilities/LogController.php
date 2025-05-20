<?php

namespace App\Http\Controllers\Utilities;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as LogFacade;

class LogController extends Controller
{
    public static function createLog($user_id = 'system', $action, $action_type = '', $action_data = '', $affected_table, $log_type = 'info', Request $request)
    {
        try {
            switch ($log_type) {
                case 'info':
                    LogFacade::info($user_id." | ".$action." | ".$action_type." | ".$action_data." | ".$affected_table);
                    break;

                case 'error':
                    LogFacade::error($user_id." | ".$action." | ".$action_type." | ".$action_data." | ".$affected_table);
                    break;

                case 'warning':
                    LogFacade::warning($user_id." | ".$action." | ".$action_type." | ".$action_data." | ".$affected_table);
                    break;

                case 'debug':
                    LogFacade::debug($user_id." | ".$action." | ".$action_type." | ".$action_data." | ".$affected_table);
                    break;

                case 'critical':
                    LogFacade::critical($user_id." | ".$action." | ".$action_type." | ".$action_data." | ".$affected_table);
                    break;

                default:
                    LogFacade::info($user_id." | ".$action." | ".$action_type." | ".$action_data." | ".$affected_table);
                    break;
            }
            $new_log = new Log();
            $new_log->user_id = $user_id;
            $new_log->ip = $request->ip();
            $new_log->user_agent = $request->server('HTTP_USER_AGENT');
            $new_log->url = $request->fullUrl();
            $new_log->method = $request->method();
            $new_log->action = $action;
            $new_log->action_type = $action_type;
            $new_log->action_data = $action_data;
            $new_log->affected_table = $affected_table;
            $new_log->save();
            return true;
        } catch (\Throwable $th) {
            LogFacade::error($th);
        }
    }
}
