<?php

namespace App\Helpers;


if (!function_exists('wEchoFullProfileImage')) {
    function wEchoFullProfileImage($user)
    {
        return '<img src="' . $user->full_profile_image . '" alt="" class="img-circle img-size-32 mr-2">';
    }
}


if (!function_exists('wEchoUserStatus')) {
    function wEchoUserStatus($status, $is_badges = false)
    {
        $statuses = [
            config('constants.users_status.pending') => "Pendiente",
            config('constants.users_status.approved') => "Aprobado",
            config('constants.users_status.banned') => "Banneado",
            config('constants.users_status.rejected') => "Rejected",
        ];
        if (empty($statuses[$status])) {
            return "---";
        }
        if (empty($is_badges)) {
            return $statuses[$status];
        }

        $statuses_styles = [
            config('constants.users_status.pending') => "warning",
            config('constants.users_status.approved') => "success",
            config('constants.users_status.banned') => "danger",
            config('constants.users_status.rejected') => "dark",
        ];


        return "<span class=\"badge badge-" . $statuses_styles[$status] . "\">" . $statuses[$status] . "</span>";

    }
}


if (!function_exists('wEchoPlan')) {
    function wEchoPlan($status, $is_badges = false)
    {
        $statuses = [
            'free' => "Gratis",
            'pro' => "Pro",
            'enterprise' => "Enterprise",
        ];
        if (empty($statuses[$status])) {
            return "---";
        }
        if (empty($is_badges)) {
            return $statuses[$status];
        }

        $statuses_styles = [
            "free"=> "warning",
            'pro' => "success",
            'enterprise' => "info",
        ];


        return "<span class=\"badge badge-" . $statuses_styles[$status] . "\">" . $statuses[$status] . "</span>";

    }
}


if (!function_exists('wEchoDeliveryOrderStatus')) {
    function wEchoDeliveryOrderStatus($status, $is_badges = false)
    {
        $statuses = [
            config('constants.delivery_order_status.no_scan') => "Sin Escanear",
            config('constants.delivery_order_status.on_delivery') => "En Reparto",
            config('constants.delivery_order_status.warehouse') => "En depósito",
            config('constants.delivery_order_status.rebound') => "Rebotado",
            config('constants.delivery_order_status.second_visit') => "Segunda Visita",
            config('constants.delivery_order_status.rejected') => "Rechazado",
            config('constants.delivery_order_status.delivered') => "Entregado",
            config('constants.delivery_order_status.destroy') => "Desarme",
            config('constants.delivery_order_status.destroy_delivered') => "Entregada para desarme",

        ];

        if (empty($is_badges)) {
            if (empty($statuses[$status])) {
                return "";
            }

            return $statuses[$status];
        }

        $statuses_styles = [
            config('constants.delivery_order_status.on_truck') => "gray",
            config('constants.delivery_order_status.no_scan') => "gray",
            config('constants.delivery_order_status.on_delivery') => "indigo",
            config('constants.delivery_order_status.second_visit') => "fuchsia",
            config('constants.delivery_order_status.warehouse') => "orange",
            config('constants.delivery_order_status.rebound') => "maroon",
            config('constants.delivery_order_status.rejected') => "red",
            config('constants.delivery_order_status.delivered') => "success",
            config('constants.delivery_order_status.destroy') => "gray-dark",
            config('constants.delivery_order_status.destroy_delivered') => "black",
        ];
        if (empty($statuses[$status])) {
            return "";
        }


        return "<span class=\"badge bg-" . $statuses_styles[$status] . "\">" . $statuses[$status] . "</span>";

    }
}

if (!function_exists('wEchoDeliveryOrderImportLogsStatus')) {
    function wEchoDeliveryOrderImportLogsStatus($status, $is_badges = false)
    {
        $statuses = [
            config('constants.delivery_order_import_logs_status.processing') => "Procesando",
            config('constants.delivery_order_import_logs_status.finished') => "Finalizada",

        ];

        if (empty($is_badges)) {
            return $statuses[$status];
        }

        $statuses_styles = [
            config('constants.delivery_order_import_logs_status.processing') => "indigo",
            config('constants.delivery_order_import_logs_status.finished') => "success",
        ];

        return "<span class=\"badge bg-" . $statuses_styles[$status] . "\">" . $statuses[$status] . "</span>";

    }
}

if (!function_exists('wEchoPushNotificationType')) {
    function wEchoPushNotificationType($status, $is_badges = false)
    {
        $statuses = [
            'new_patient_form_completed' => "Formulario Paciente",
            'new_patien_form_completed' => "Formulario Paciente",
            'new_medical_prescription_request' => "Nueva solicitud de receta",
            'manual_push' => "Push Manual",
        ];

        if (empty($statuses[$status])) {
            return $status;
        }


        if (empty($is_badges)) {
            return $statuses[$status];
        }

        $statuses_styles = [
            'new_patient_form_completed' => "success",
            'new_patien_form_completed' => "success",
            'new_medical_prescription_request' => "warning",
            'manual_push' => "danger",
        ];


        return "<span class=\"badge badge-" . $statuses_styles[$status] . "\">" . $statuses[$status] . "</span>";

    }
}


if (!function_exists('wEchoAppointmentType')) {
    function wEchoAppointmentType($status, $is_badges = false)
    {
        $statuses = [
            1 => "Presencial",
            2 => "Virtual",
            3 => "Mixto",
        ];

        if(empty($status[$status])){
            return "";

        }
        if (empty($is_badges)) {
            return $statuses[$status];
        }

        $statuses_styles = [
            1 => "warning",
            2 => "success",
            3 => "dark",
        ];


        return "<span class=\"badge badge-" . $statuses_styles[$status] . "\">" . $statuses[$status] . "</span>";

    }
}



if (!function_exists('wEchoShortStringTooltip')) {
    function wEchoShortStringTooltip($string, $limit = 20)
    {
        if (strlen($string) > $limit) {
            return '<span data-toggle="tooltip" data-placement="top" title="' . $string . '">' . substr($string, 0, $limit) . '...</span>';
        }
        return $string;
    }
}


if (!function_exists('wEchoShortDateTooltip')) {
    function wEchoShortDateTooltip($date)
    {
        return '<span data-toggle="tooltip" data-placement="top" title="' . showDateInLocalTimezone($date) . '">' . showDateInLocalTimezone($date,"H:i") . '</span>';
    }
}
if (!function_exists('getReformattedAppointmentStatus')) {
    function getReformattedAppointmentStatus()
    {
        $statuses = config('constants.appointment_status');
        $reformatted_statuses = [];

        // Convertir el array numérico en un array asociativo con el 'id' como clave
        foreach ($statuses as $status) {
            $reformatted_statuses[$status['id']] = $status['name'];
        }

        return $reformatted_statuses;
    }
}


if (!function_exists('wEchoAppointmentStatus')) {
    function wEchoAppointmentStatus($status, $is_badges = false)
    {
        // Obtener el array reformateado con los ids como claves
        $statuses = getReformattedAppointmentStatus();

        // Definir los colores de los estados
        $statuses_styles = [
            'scheduled' => "badge-primary", // Tomado
            'confirmed' => "badge-info", // Confirmado
            'waiting_room' => "badge-light", // Sala de Espera
            'attending' => "badge-purple", // Atendiendo
            'completed' => "badge-secondary", // Atendido
            'patient_missed' => "badge-danger", // Inasistencia Paciente
            'patient_canceled' => "badge-light", // Cancelado por el paciente
            'medic_canceled' => "badge-light", // Cancelado por el médico
        ];

        // Verificar si el estado es válido
        if (empty($status) || !isset($statuses[$status])) {
            return "<span class=\"badge badge-secondary\">Estado no definido</span>";
        }

        // Si no es para badges, devolver solo el nombre del estado
        if (empty($is_badges)) {
            return $statuses[$status];
        }

        // Devolver el badge con el estilo correspondiente
        return "<span class=\"badge " . $statuses_styles[$status] . "\">" . $statuses[$status] . "</span>";
    }
}

if (!function_exists('wEchoAppointmentPaymentStatus')) {
    function wEchoAppointmentPaymentStatus($status, $is_badges = false)
    {
        // Obtener el array reformateado con los ids como claves
        $statuses = [
            "not_apply" => "No aplica",
            "pending" => "Pendiente",
            "paid" => "Pagado",

        ];

        // Definir los colores de los estados
        $statuses_styles = [
            "not_apply" => "badge-secondary", // No aplica
            "pending" => "badge-warning", // Pendiente
            "paid" => "badge-success", // Pagado
        ];

        // Verificar si el estado es válido
        if (empty($status) || !isset($statuses[$status])) {
            return "<span class=\"badge badge-secondary\">Estado no definido</span>";
        }

        // Si no es para badges, devolver solo el nombre del estado
        if (empty($is_badges)) {
            return $statuses[$status];
        }

        // Devolver el badge con el estilo correspondiente
        return "<span class=\"badge " . $statuses_styles[$status] . "\">" . $statuses[$status] . "</span>";
    }
}


