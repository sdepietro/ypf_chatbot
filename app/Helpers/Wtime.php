<?php
//app/Helpers/Envato/User.php
namespace App\Helpers;


use App\Models\Config;
use Carbon\Carbon;

/**
 * Convierte una fecha UTC en formato MySQL a formato para DateTimePicker en zona horaria de Argentina
 *
 * @param string|null $date Fecha en formato 'Y-m-d H:i:s' en UTC
 * @return string|null Fecha en formato 'Y-m-d H:i' en zona horaria Argentina/Buenos_Aires o null si está vacía
 *
 * @example
 * Input: '2024-01-15 14:30:00'
 * Output: '2024-01-15 11:30'
 *
 * @example
 * Input: '2024-06-20 18:00:00'
 * Output: '2024-06-20 15:00'
 *
 * @example
 * Input: null
 * Output: null
 */
if (!function_exists('getToDateTimePicker')) {
    function getToDateTimePicker($date)
    {
        if (empty($date)) {
            return $date;
        }
        return Carbon::createFromFormat('Y-m-d H:i:s', $date, 'UTC')->setTimezone('America/Argentina/Buenos_Aires')->format('Y-m-d H:i');
    }
}

/**
 * Convierte una fecha UTC en formato MySQL a zona horaria de Argentina con formato personalizable
 *
 * @param string|null $date Fecha en formato 'Y-m-d H:i:s' en UTC
 * @param string $format Formato de salida deseado (por defecto 'd/m/Y H:i')
 * @return string Fecha formateada en zona horaria Argentina/Buenos_Aires o "Sin fecha" si está vacía
 *
 * @example
 * Input: ('2024-01-15 14:30:00', 'd/m/Y H:i')
 * Output: '15/01/2024 11:30'
 *
 * @example
 * Input: ('2024-06-20 18:00:00', 'd/m/Y')
 * Output: '20/06/2024'
 *
 * @example
 * Input: (null, 'd/m/Y H:i')
 * Output: 'Sin fecha'
 */
if (!function_exists('showDateInLocalTimezone')) {
    function showDateInLocalTimezone($date, $format = 'd/m/Y H:i')
    {
        if (empty($date)) {
            return "Sin fecha";
        }
        return Carbon::createFromFormat('Y-m-d H:i:s', $date, 'UTC')->setTimezone('America/Argentina/Buenos_Aires')->format($format);
    }
}
/**
 * Convierte una fecha UTC a zona horaria de Argentina para uso en emails, con manejo de errores
 * Similar a showDateInLocalTimezone pero usa Carbon::parse() para mayor flexibilidad en el formato de entrada
 *
 * @param string|null $date Fecha en cualquier formato reconocible por Carbon
 * @param string $format Formato de salida deseado (por defecto 'd/m/Y H:i')
 * @return string Fecha formateada en zona horaria Argentina/Buenos_Aires, "Sin fecha" o "Fecha inválida"
 *
 * @example
 * Input: ('2024-01-15 14:30:00', 'd/m/Y H:i')
 * Output: '15/01/2024 11:30'
 *
 * @example
 * Input: ('2024-06-20T18:00:00Z', 'd/m/Y H:i')
 * Output: '20/06/2024 15:00'
 *
 * @example
 * Input: ('fecha-invalida', 'd/m/Y H:i')
 * Output: 'Fecha inválida'
 */
if (!function_exists('showDateInLocalTimezoneEmail')) {

    function showDateInLocalTimezoneEmail($date, $format = 'd/m/Y H:i')
    {
        if (empty($date)) {
            return "Sin fecha";
        }

        try {
            // Parsear la fecha en UTC
            $utcDate = Carbon::parse($date, 'UTC');

            // Convertir a la zona horaria de Buenos Aires y formatear
            $localDate = $utcDate->setTimezone('America/Argentina/Buenos_Aires');

            return $localDate->format($format);
        } catch (\Exception $e) {
            // En caso de error, puedes devolver un mensaje o manejarlo según lo necesites
            return "Fecha inválida";
        }
    }
}


/**
 * Convierte una fecha UTC (en cualquier formato string) a zona horaria de Argentina usando timestamp
 * Útil cuando el formato de entrada es desconocido o variable
 *
 * @param string|null $date Fecha en cualquier formato string reconocible por strtotime()
 * @return string Fecha en formato 'd/m/Y H:i' en zona horaria Argentina/Buenos_Aires o "Sin fecha"
 *
 * @example
 * Input: '2024-01-15 14:30:00'
 * Output: '15/01/2024 11:30'
 *
 * @example
 * Input: '2024-06-20 18:00:00'
 * Output: '20/06/2024 15:00'
 *
 * @example
 * Input: ''
 * Output: 'Sin fecha'
 */
if (!function_exists('showDateUTCInLocalTimezone')) {
    function showDateUTCInLocalTimezone($date)
    {
        if (empty($date)) {
            return "Sin fecha";
        }
        return Carbon::createFromTimestamp(strtotime($date))->setTimezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i');
    }
}

/**
 * Convierte una fecha desde zona horaria de Argentina a UTC en formato MySQL
 * Útil para guardar fechas locales argentinas en la base de datos como UTC
 *
 * @param string|null $date Fecha en formato 'Y-m-d H:i:s' en zona horaria Argentina/Buenos_Aires
 * @return string Fecha en formato 'Y-m-d H:i:s' en UTC o "Sin fecha"
 *
 * @example
 * Input: '2024-01-15 11:30:00'
 * Output: '2024-01-15 14:30:00'
 *
 * @example
 * Input: '2024-06-20 15:00:00'
 * Output: '2024-06-20 18:00:00'
 *
 * @example
 * Input: null
 * Output: 'Sin fecha'
 */
if (!function_exists('showDateInUTCTimezone')) {
    function showDateArgentinaInUTCTimezone($date)
    {
        if (empty($date)) {
            return "Sin fecha";
        }
        return Carbon::createFromFormat('Y-m-d H:i:s', $date, 'America/Argentina/Buenos_Aires')->setTimezone('UTC')->format('Y-m-d H:i:s');
    }
}

/**
 * Convierte una fecha UTC a una zona horaria específica con formato legible en español
 * Traduce nombres de días y meses al español usando translatedFormat
 *
 * @param string|null $date Fecha en formato 'Y-m-d H:i:s' en UTC
 * @param string $timezone Zona horaria de destino (ej: 'America/Argentina/Buenos_Aires')
 * @param string $format Formato de salida (por defecto 'l d/m/Y H:i' - día de semana completo)
 * @return string Fecha formateada con texto traducido al español o "Sin fecha"
 *
 * @example
 * Input: ('2024-01-15 14:30:00', 'America/Argentina/Buenos_Aires', 'l d/m/Y H:i')
 * Output: 'lunes 15/01/2024 11:30'
 *
 * @example
 * Input: ('2024-06-20 18:00:00', 'America/New_York', 'l d/m/Y')
 * Output: 'jueves 20/06/2024'
 *
 * @example
 * Input: (null, 'America/Argentina/Buenos_Aires', 'l d/m/Y H:i')
 * Output: 'Sin fecha'
 */
if (!function_exists('showDateInLocalTimezoneHuman')) {
    function showDateInLocalTimezoneHuman($date, $timezone, $format = 'l d/m/Y H:i')
    {
        if (empty($date)) {
            return "Sin fecha";
        }

        Carbon::setLocale('es');

        return Carbon::createFromFormat('Y-m-d H:i:s', $date, 'UTC')
            ->setTimezone($timezone)
            ->translatedFormat($format);
    }
}

/**
 * Convierte una fecha UTC en formato MySQL a formato ISO 8601 (formato estándar internacional con timezone)
 * Útil para APIs que requieren formato ISO 8601 completo
 *
 * @param string|null $date Fecha en formato 'Y-m-d H:i:s' en UTC
 * @return string Fecha en formato ISO 8601 (ej: '2024-01-15T14:30:00+00:00') o "Sin fecha"
 *
 * @example
 * Input: '2024-01-15 14:30:00'
 * Output: '2024-01-15T14:30:00+00:00'
 *
 * @example
 * Input: '2024-06-20 18:00:00'
 * Output: '2024-06-20T18:00:00+00:00'
 *
 * @example
 * Input: null
 * Output: 'Sin fecha'
 */
if (!function_exists('showDateInUTCTimezone')) {
    function showDateInUTCTimezone($date)
    {
        if (empty($date)) {
            return "Sin fecha";
        }
        return Carbon::createFromFormat('Y-m-d H:i:s', $date, 'UTC')->setTimezone('UTC')->format('c');
    }
}


/**
 * Convierte una hora desde una zona horaria específica a UTC
 * Usa el offset de timezone para calcular la diferencia horaria
 *
 * @param string $time Hora en formato string (ej: '14:30' o '14:30:00')
 * @param string $time_zone Zona horaria de origen (ej: 'America/Argentina/Buenos_Aires')
 * @param string $format Formato de salida (por defecto 'H:i')
 * @return string Hora convertida a UTC en el formato especificado
 *
 * @example
 * Input: ('14:30', 'America/Argentina/Buenos_Aires', 'H:i')
 * Output: '17:30'
 *
 * @example
 * Input: ('09:00', 'America/New_York', 'H:i:s')
 * Output: '14:00:00'
 *
 * @example
 * Input: ('23:45', 'Europe/Madrid', 'H:i')
 * Output: '21:45'
 */
if (!function_exists('convertFromTimezoneToUTC')) {
    function convertFromTimezoneToUTC($time, $time_zone, $format = "H:i")
    {
        $tz = timezone_open($time_zone);
        $dateTime = date_create("now", timezone_open("UTC"));
        return date($format, (strtotime($time) - timezone_offset_get($tz, $dateTime)));
    }
}


/**
 * Convierte una hora desde UTC a una zona horaria específica
 * Usa el offset de timezone para calcular la diferencia horaria
 *
 * @param string $time Hora en formato string UTC (ej: '14:30' o '14:30:00')
 * @param string $time_zone Zona horaria de destino (ej: 'America/Argentina/Buenos_Aires')
 * @param string $format Formato de salida (por defecto 'H:i')
 * @return string Hora convertida a la zona horaria especificada en el formato indicado
 *
 * @example
 * Input: ('17:30', 'America/Argentina/Buenos_Aires', 'H:i')
 * Output: '14:30'
 *
 * @example
 * Input: ('14:00', 'America/New_York', 'H:i:s')
 * Output: '09:00:00'
 *
 * @example
 * Input: ('21:45', 'Europe/Madrid', 'H:i')
 * Output: '23:45'
 */
if (!function_exists('convertFromUTCtoTimeZone')) {
    function convertFromUTCtoTimeZone($time, $time_zone, $format = "H:i")
    {
        $tz = timezone_open($time_zone);
        $dateTime = date_create("now", timezone_open("UTC"));
        return date($format, (strtotime($time) + timezone_offset_get($tz, $dateTime)));
    }
}


/**
 * Convierte una fecha en formato ISO 8601 a formato MySQL UTC
 * Útil para procesar fechas de APIs externas o frontends que envían fechas ISO
 *
 * @param string $time Fecha en formato ISO 8601 (ej: '2024-01-15T14:30:00-03:00')
 * @return string Fecha en formato MySQL 'Y-m-d H:i:s' en UTC
 *
 * @example
 * Input: '2024-01-15T14:30:00-03:00'
 * Output: '2024-01-15 17:30:00'
 *
 * @example
 * Input: '2024-06-20T18:00:00Z'
 * Output: '2024-06-20 18:00:00'
 *
 * @example
 * Input: '2024-03-10T09:15:00+01:00'
 * Output: '2024-03-10 08:15:00'
 */
//Esta es la que se usa para poder pasar de ISO a date time de mysql
if (!function_exists('convertFromISOtoMYSQLUTC')) {
    function convertFromISOtoMYSQLUTC($time)
    {
        return Carbon::parse($time)->setTimezone('UTC')->format('Y-m-d H:i:s');
    }
}


/**
 * Obtiene el nombre del día de la semana en español a partir de su número
 * Donde 0 = Domingo, 1 = Lunes, ..., 6 = Sábado
 *
 * @param int $dayNumber Número del día de la semana (0-6, donde 0 es Domingo)
 * @return string Nombre del día en español
 *
 * @example
 * Input: 0
 * Output: 'Domingo'
 *
 * @example
 * Input: 3
 * Output: 'Miércoles'
 *
 * @example
 * Input: 6
 * Output: 'Sábado'
 */
//Esta es la que se usa para poder pasar de ISO a date time de mysql
if (!function_exists('getDayOfWeek')) {
    function getDayOfWeek($dayNumber)
    {

        $days = [
            "Domingo",
            "Lunes",
            "Martes",
            "Miércoles",
            "Jueves",
            "Viernes",
            "Sábado",
        ];


        return $days[$dayNumber];
    }
}


/**
 * Calcula la cantidad de días completos entre dos fechas UTC
 * Útil para calcular diferencias de tiempo, vencimientos, o periodos
 *
 * @param string $date1 Primera fecha en formato 'Y-m-d H:i:s' en UTC
 * @param string $date2 Segunda fecha en formato 'Y-m-d H:i:s' en UTC
 * @return int Cantidad de días completos entre las dos fechas (siempre positivo)
 *
 * @example
 * Input: ('2024-01-15 10:00:00', '2024-01-20 10:00:00')
 * Output: 5
 *
 * @example
 * Input: ('2024-01-01 14:30:00', '2024-01-01 18:30:00')
 * Output: 0
 *
 * @example
 * Input: ('2024-01-20 10:00:00', '2024-01-15 10:00:00')
 * Output: 5
 */
// funcion que recibe 2 fechas. y me devuelve la cantidad de días que pasaron entre la primera fecha y la segunda fecha
if (!function_exists('wGetDaysBetweenDates')) {
    function wGetDaysBetweenDates($date1, $date2)
    {
        $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $date1, 'UTC');
        $date2 = Carbon::createFromFormat('Y-m-d H:i:s', $date2, 'UTC');
        return $date1->diffInDays($date2);
    }
}


/**
 * Calcula la cantidad de minutos entre dos fechas UTC
 * Útil para calcular duraciones de citas, tiempos de espera, o intervalos precisos
 *
 * @param string $date1 Primera fecha en formato 'Y-m-d H:i:s' en UTC
 * @param string $date2 Segunda fecha en formato 'Y-m-d H:i:s' en UTC
 * @return int Cantidad de minutos entre las dos fechas (siempre positivo)
 *
 * @example
 * Input: ('2024-01-15 10:00:00', '2024-01-15 11:30:00')
 * Output: 90
 *
 * @example
 * Input: ('2024-01-15 14:00:00', '2024-01-15 14:45:00')
 * Output: 45
 *
 * @example
 * Input: ('2024-01-15 10:00:00', '2024-01-16 10:00:00')
 * Output: 1440
 */
// funcion que recibe 2 fechas. y me devuelve la cantidad de minutos que pasaron entre la primera fecha y la segunda fecha
if (!function_exists('wGetMinutesBetweenDates')) {
    function wGetMinutesBetweenDates($date1, $date2)
    {
        $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $date1, 'UTC');
        $date2 = Carbon::createFromFormat('Y-m-d H:i:s', $date2, 'UTC');
        return $date1->diffInMinutes($date2);
    }
}


/**
 * Convierte la diferencia entre dos fechas en un formato legible en español
 * Devuelve la diferencia en términos humanos (ej: "2 días 3 horas", "1 mes 2 semanas")
 *
 * @param string $start_date Fecha de inicio en cualquier formato reconocible por strtotime()
 * @param string $end_date Fecha de fin en cualquier formato reconocible por strtotime()
 * @param int $max_units Cantidad máxima de unidades de tiempo a mostrar (por defecto 2)
 * @return string Diferencia de tiempo en formato legible o "No definido"
 *
 * @example
 * Input: ('2024-01-15 10:00:00', '2024-01-17 13:30:00', 2)
 * Output: '2 días 3 horas'
 *
 * @example
 * Input: ('2024-01-15 10:00:00', '2024-01-15 10:45:00', 2)
 * Output: '45 minutos'
 *
 * @example
 * Input: ('2024-01-01 00:00:00', '2024-03-15 14:30:00', 3)
 * Output: '2 meses 2 semanas 15 horas'
 */
if (!function_exists('get_friendly_time_between_dates')) {
    function get_friendly_time_between_dates($start_date, $end_date, $max_units = 2)
    {
        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);
        $time_difference = $end_timestamp - $start_timestamp;

        $tokens = [
            31536000 => 'año',
            2592000 => 'mes',
            604800 => 'semana',
            86400 => 'día',
            3600 => 'hora',
            60 => 'minuto',
            1 => 'segundo'
        ];

        $i = 0;
        $responses = [];
        while ($i < $max_units && $time_difference > 0) {
            foreach ($tokens as $unit => $text) {
                if ($time_difference < $unit) continue;

                $number_of_units = floor($time_difference / $unit);
                $responses[] = $number_of_units . ' ' . $text . ($number_of_units > 1 ? 's' : '');
                $time_difference -= $unit * $number_of_units;
                $i++;
            }
        }

        if (!empty($responses)) {
            return implode(' ', $responses);
        }

        return 'No definido';
    }
}


/**
 * Convierte una fecha del formato dd/mm/yyyy (formato español) a formato MySQL yyyy-mm-dd
 * Útil para procesar fechas ingresadas por usuarios en formato argentino/español
 *
 * @param string|null $date Fecha en formato 'd/m/Y' (ej: '15/01/2024')
 * @return string|null Fecha en formato 'Y-m-d' (ej: '2024-01-15') o null si está vacía
 *
 * @example
 * Input: '15/01/2024'
 * Output: '2024-01-15'
 *
 * @example
 * Input: '20/06/2024'
 * Output: '2024-06-20'
 *
 * @example
 * Input: null
 * Output: null
 */
//funcion quye recibe una fecha en formato dd/mm/yyyy y la devuelve en formato yyyy-mm-dd
if (!function_exists('convertFromDDMMYYYToMysql')) {
    function convertFromDDMMYYYToMysql($date)
    {
        if (empty($date)) {
            return $date;
        }
        return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
    }
}


/**
 * Convierte una fecha desde formato MySQL (yyyy-mm-dd) a un formato personalizado
 * Por defecto devuelve formato español dd/mm/yyyy, pero acepta cualquier formato
 *
 * @param string|null $date Fecha en formato 'Y-m-d' (ej: '2024-01-15')
 * @param string $format Formato de salida deseado (por defecto 'd/m/Y')
 * @return string Fecha en el formato especificado o "Sin fecha"
 *
 * @example
 * Input: ('2023-05-26', 'd/m/Y')
 * Output: '26/05/2023'
 *
 * @example
 * Input: ('2024-01-15', 'd/m/Y H:i')
 * Output: '15/01/2024 00:00'
 *
 * @example
 * Input: (null, 'd/m/Y')
 * Output: 'Sin fecha'
 */
// 2023-05-26 => 26/05/2023
if (!function_exists('showDateFromMySqlToFormat')) {
    function showDateFromMySqlToFormat($date, $format = 'd/m/Y')
    {
        if (empty($date)) {
            return "Sin fecha";
        }
        return Carbon::createFromFormat('Y-m-d', $date)->format($format);
    }
}
