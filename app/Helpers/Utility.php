<?php

namespace App\Helpers;


use App\Models\Marketing_log;
use App\Models\Prescription_profiles;
use App\Models\Scheduled_reminder;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

if (!function_exists('generateRandomString')) {
    function generateRandomString($length = 10)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}


if (!function_exists('wSendEmail')) {
    function wSendEmail($emailTo, $subject, $content, $clinic = null, $preheader = "", $schedule_reminder_id = null, $pixel_type = null, $attachs_by_url = [], $attachs_by_file = [], $base_email = 'emails.base_template')
    {
        $send_email = wGetConfigs('enable-emails');
        //guardamos en el storage/logs $send_email
//        \Log::info('send_email', ['send_email' => $send_email]);


        if (empty($send_email)) {
            return true;
        }

        $emailFrom = config('mail.from.address');
        $nameFrom = !empty($clinic->name) && empty($clinic->medic_id) ? $clinic->name : config('mail.from.name');

        if (!empty($schedule_reminder_id)) {
            if (empty($pixel_type) || $pixel_type == "schedule_reminder") {
                $content .= "<img src='" . route('pixel_tracking') . "?schedule_reminder_id=" . $schedule_reminder_id . "'>";
            }

            if ($pixel_type == "marketing") {
                $content .= "<img src='" . route('pixel_marketing_tracking') . "?marketing_log_id=" . $schedule_reminder_id . "'>";
            }

        }

        $data = [
            'title' => $subject,
            'preheader' => $preheader,
            'clinic' => $clinic,
            'content' => $content
        ];

        try {
            Mail::send($base_email, $data, function ($message) use ($emailTo, $nameFrom, $emailFrom, $subject, $attachs_by_url, $attachs_by_file) {
                $message->from($emailFrom, $nameFrom);
                $message->to($emailTo);
                $message->subject($subject);

                // Forzar header MIME-Version explícitamente
                $message->getHeaders()->addTextHeader('MIME-Version', '1.0');

                foreach ($attachs_by_url as $k => $attach) {
                    $message->attachData(file_get_contents($attach->url), $attach->name);
                }

                foreach ($attachs_by_file as $k => $attach_by_file) {
                    $message->attachData($attach_by_file->file, $attach_by_file->name);
                }

                $headers = $message->getHeaders();
                $headers->addTextHeader('o:tracking', 'no');          // desactiva tracking general
                $headers->addTextHeader('o:tracking-clicks', 'no');   // desactiva click tracking
                $headers->addTextHeader('o:tracking-opens', 'no');    // desactiva open tracking


            });
            return true;
        } catch (\Exception $e) {
            if (!empty($schedule_reminder_id)) {
                if (empty($pixel_type) || $pixel_type == "schedule_reminder") {
                    $scheduleReminder = Scheduled_reminder::find($schedule_reminder_id);
                    if (!empty($scheduleReminder)) {
                        $scheduleReminder->error_result = $e->getMessage();
                        $scheduleReminder->email_status = config('constants.email_status.fail');
                        $scheduleReminder->save();
                    }
                }

                if ($pixel_type == "marketing") {
                    $marketingLog = Marketing_log::find($schedule_reminder_id);
                    if (!empty($marketingLog)) {
                        $marketingLog->email_status = config('constants.email_status.fail');
                        $marketingLog->save();
                    }
                }

                $webhookUrl = config('constants.slack_notification_webhook');
                $response = Http::post($webhookUrl, [
                    'text' => 'Hubo un problema con el envío de e-mails. Error: ' . $e->getMessage() . ' - Email a: ' . $emailTo . ' - Asunto: ' . $subject,
                ]);


            }
            //echo $e->getMessage();
            \Log::info('send_email', ['error' => $e->getMessage()]);


            return false;
        }


    }
}

if (!function_exists('wGetFullProfileImage')) {
    function wGetFullProfileImage($profile_image)
    {
        if (empty($profile_image)) {
            return asset(config('constants.image_sizes.profile_image.default'));
        }
        $profile_image = str_replace('//', '/', $profile_image);
        return config('constants.default_filesystem_public_url') . $profile_image;
    }
}

if (!function_exists('wGetFullClinicHistoryTypeIcon')) {
    function wGetFullClinicHistoryTypeIcon($image)
    {
        if (empty($image)) {
            return null;
        }
        return asset($image);

    }
}

if (!function_exists('wGetFullClinicImage')) {
    function wGetFullClinicImage($image)
    {
        if (empty($image)) {
            return asset(config('constants.image_sizes.clinic.default'));
        }
        return asset($image);
    }
}

if (!function_exists('checkAppointmentStatus')) {
    function checkAppointmentStatus($statusCheck)
    {
        $status = config('constants.appointment_status');

        foreach ($status as $st) {
            if ($st['id'] == $statusCheck) {
                return true;
            }
        }
        return false;

    }
}


if (!function_exists('checkAppointmentStatus')) {
    function checkAppointmentPaymentStatus($statusCheck)
    {
        $status = config('constants.appointment_payment_status');

        foreach ($status as $st) {
            if ($st['id'] == $statusCheck) {
                return true;
            }
        }
        return false;

    }
}


if (!function_exists('wGetFullSignatureImage')) {
    function wGetFullSignatureImage($signature_image)
    {
        if (empty($signature_image)) {
            return null;
        }
        return config('constants.default_filesystem_public_url') . $signature_image;

    }
}

if (!function_exists('wGetFullmedicalCertificateUrl')) {
    function wGetFullmedicalCertificateUrl($url)
    {
        if (empty($url)) {
            return "";
        }
        return asset($url);

    }
}


if (!function_exists('wGetFullmedicalPrescriptionUrl')) {
    function wGetFullmedicalPrescriptionUrl($url)
    {
        if (empty($url)) {
            return "";
        }
        return config('constants.default_filesystem_public_url') . $url;

    }
}

if (!function_exists('wGetFullMedicalInsuranceLogoUrl')) {
    function wGetFullMedicalInsuranceLogoUrl($url)
    {
        if (empty($url)) {
            return null;
        }
        return config('constants.default_filesystem_public_url') . $url;
    }
}

if (!function_exists('wGetFullLogoImage')) {
    function wGetFullLogoImage($image)
    {
        if (empty($image)) {
            return null;
        }
        return config('constants.default_filesystem_public_url') . $image;
    }
}

if (!function_exists('wGetFullLogoImageS3')) {
    function wGetFullLogoImageS3($profile_image)
    {
        if (empty($profile_image)) {
            return null;
        }
        return config('constants.default_filesystem_public_url') . $profile_image;

    }
}


if (!function_exists('wGetFullIdentificationImage')) {
    function wGetFullIdentificationImage($profile_image)
    {
        if (empty($profile_image)) {
            return null;
        }
        return config('constants.default_filesystem_public_url') .'/'. $profile_image;
    }
}


if (!function_exists('printProvOrNac')) {
    function printProvOrNac(Prescription_profiles $prescriptionProfile)
    {
        $matricula = $prescriptionProfile->type;
        if ($matricula == "MP") {
            return "Prov.";
        }
        if ($matricula == "MN") {

            return "Nac.";
        }
        return "";
    }
}


if (!function_exists('w_number_to_word')) {
    function w_number_to_word($number)
    {
        $numbers_array = ["cero",
            "uno",
            "dos",
            "tres",
            "cuatro",
            "cinco",
            "seis",
            "siete",
            "ocho",
            "nueve",
            "diez",
            "once",
            "doce",
            "trece",
            "catorce",
            "quince",
            "dieciséi",
            "diecisiete",
            "dieciocho",
            "diecinueve",
            "veinte",
            "veintiun",
            "ntiun",
            "veintidó",
            "veintitré",
            "veinticuatro",
            "veinticinc",
            "veintiséi",
            "veintisiete",
            "veintiocho",
            "veintinueve",
            "treinta",
            "treinta y un",
            "treinta y dos",
            "treinta y tres",
            "treinta y cuatro",
            "treinta y cinco",
            "treinta y seis",
            "treinta y siete",
            "treinta y ocho",
            "treinta y nueve",
            "cuarenta",
            "cuarenta y un",
            "cuarenta y dos",
            "cuarenta y tres",
            "cuarenta y cuatro",
            "cuarenta y cinco",
            "cuarenta y seis",
            "cuarenta y siete",
            "cuarenta y ocho",
            "cuarenta y nueve",
            "cincuenta",
            "cincuenta y dos",
            "cincuenta y tres",
            "cincuenta y cuatro",
            "cincuenta y cinco",
            "cincuenta y seis",
            "cincuenta y siete",
            "cincuenta y ocho",
            "cincuenta y nueve",
            "sesenta",
            "sesenta y dos",
            "sesenta y tres",
            "sesenta y cuatro",
            "sesenta y cinco",
            "sesenta y seis",
            "sesenta y siete",
            "sesenta y ocho",
            "sesenta y nueve",
            "setenta",
            "setenta y dos",
            "setenta y tres",
            "setenta y cuatro",
            "setenta y cinco",
            "setenta y seis",
            "setenta y siete",
            "setenta y ocho",
            "setenta y nueve",
            "ochenta",
            "ochenta y dos",
            "ochenta y tres",
            "ochenta y cuatro",
            "ochenta y cinco",
            "ochenta y seis",
            "ochenta y siete",
            "ochenta y ocho",
            "ochenta y nueve",
            "noventa",
            "noventa y dos",
            "noventa y tres",
            "noventa y cuatro",
            "noventa y cinco",
            "noventa y seis",
            "noventa y siete",
            "noventa y ocho",
            "noventa y nueve"];

        if (empty($numbers_array[$number])) {
            return "";
        }

        return $numbers_array[$number];
    }
}


if (!function_exists('w_tooltip_help')) {
    function w_tooltip_help($text = null)
    {
        return '<span class="tooltip-help" data-toggle="tooltip" data-placement="top" title="' . $text . '"><i class="fa fa-question-circle"></i></span>';

    }
}

if (!function_exists('w_tooltip_info')) {
    function w_tooltip_info($text = null)
    {
        return '<span class="tooltip-help" data-toggle="tooltip" data-placement="top" title="' . $text . '"><i class="fa fa-info-circle"></i></span>';

    }
}


if (!function_exists('print_query_with_bindings')) {
    function print_query_with_bindings($query)
    {
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        // Cada binding debe ser adecuadamente escapado
        foreach ($bindings as $binding) {
            $value = is_numeric($binding) ? $binding : "'" . addslashes($binding) . "'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }

        echo $sql;
        die(";      ----FIN DE LA QUERY");
    }
}

if (!function_exists('print_query_with_bindings')) {

    function isValidHttpsUrl($url)
    {
        // Verificar si la URL tiene el formato correcto y empieza con "https"
        return filter_var($url, FILTER_VALIDATE_URL) &&
            preg_match('/^https:\/\//', $url);
    }
}


//funcion para determinar si es mobile o no
if (!function_exists('w_is_mobile')) {
    function w_is_mobile()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_array = array('iphone', 'ipad', 'android', 'blackberry', 'nokia', 'opera mini', 'windows mobile', 'windows phone', 'iemobile');
        foreach ($mobile_array as $value) {
            if (stripos($user_agent, $value) !== false) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('w_is_valid_mail')) {
    function w_is_valid_mail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}


if (!function_exists('wGetExcelColumnName')) {

    function wGetExcelColumnName($index)
    {
        $columnName = '';

        while ($index >= 0) {
            $remainder = $index % 26;
            $columnName = chr(65 + $remainder) . $columnName;
            $index = floor($index / 26) - 1;
        }

        return $columnName;
    }
}


if (!function_exists('wUpdateFields')) {
    function wUpdateFields(Request $request, $field, $object)
    {
        if ($request->has($field)) {
            return $request->input($field);
        } elseif (isset($object->$field)) {
            return $object->$field;
        }
    }
}

if (!function_exists('wGenerateTextImage')) {
    function wGenerateTextImage(string $text, int $fontSize = 12, string $fontColor = '000000'): string
    {
        $font = public_path('ttf/arial.ttf');

        // Medidas
        $bbox = imagettfbbox($fontSize, 0, $font, strtoupper($text));
        $textWidth = abs($bbox[4] - $bbox[0]);
        $textHeight = abs($bbox[1] - $bbox[7]);

        // Ancho y alto con márgenes mínimos
        $padding = 4;
        $imageWidth = $textWidth + ($padding * 2);
        $imageHeight = $textHeight + 6;

        // Imagen final
        $image = imagecreatetruecolor($imageWidth, $imageHeight);

        // Colores
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, hexdec(substr($fontColor, 0, 2)), hexdec(substr($fontColor, 2, 2)), hexdec(substr($fontColor, 4, 2)));

        // Fondo blanco
        imagefill($image, 0, 0, $white);

        // Posición
        $x = $padding;
        $y = $textHeight + 2;

        imagettftext($image, $fontSize, 0, $x, $y, $black, $font, strtoupper($text));

        // Convertir a base64
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();

        return 'data:image/png;base64,' . base64_encode($imageData);
    }
}



