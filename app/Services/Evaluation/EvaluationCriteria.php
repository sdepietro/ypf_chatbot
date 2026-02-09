<?php

namespace App\Services\Evaluation;

class EvaluationCriteria
{
    public static function all(): array
    {
        return [
            [
                'key' => 'greeting',
                'number' => 1,
                'name' => 'Saludo amable y profesional',
                'description' => 'El playero saluda al cliente con "buen dia/tarde/noche", le da la bienvenida, y/o lo llama por su nombre si lo conoce.',
                'good_examples' => [
                    'Buen dia! Bienvenido a YPF, en que lo puedo ayudar?',
                    'Buenas tardes! Como le va? Que necesita?',
                ],
                'bad_examples' => [
                    'Que necesitas?',
                    'Hola. (sin calidez ni bienvenida)',
                ],
            ],
            [
                'key' => 'focus_on_client',
                'number' => 2,
                'name' => 'Foco exclusivo en el cliente',
                'description' => 'El playero demuestra atencion, observacion y escucha activa. No se distrae y se concentra en lo que el cliente necesita.',
                'good_examples' => [
                    'Entiendo, asi que necesita diesel 500, verdad?',
                    'Perfecto, ya le cargo el tanque completo como me pidio.',
                ],
                'bad_examples' => [
                    'Ignorar lo que el cliente pidio y hacer otra cosa.',
                    'Responder con informacion que no tiene que ver con lo que el cliente pregunto.',
                ],
            ],
            [
                'key' => 'persuasion',
                'number' => 3,
                'name' => 'Gatillo mental / persuasion',
                'description' => 'El playero comunica con claridad, destaca beneficios, muestra empatia y adapta su trato al tipo de cliente.',
                'good_examples' => [
                    'La Infinia le cuida mas el motor, muchos clientes notan la diferencia.',
                    'Entiendo que esta apurado, le cargo rapido asi sale enseguida.',
                ],
                'bad_examples' => [
                    'Respuestas genericas sin adaptarse al cliente.',
                    'No comunicar ningun beneficio durante toda la interaccion.',
                ],
            ],
            [
                'key' => 'reciprocity',
                'number' => 4,
                'name' => 'Principio de reciprocidad',
                'description' => 'El playero ayuda sin que se lo pidan, se anticipa a necesidades y acompana al cliente durante la carga.',
                'good_examples' => [
                    'Mientras le cargo, quiere que le limpie el parabrisas?',
                    'Le reviso la presion de las ruedas sin costo, aprovechando la espera.',
                ],
                'bad_examples' => [
                    'Solo hacer lo minimo que le pidieron sin ofrecer nada adicional.',
                    'Quedarse parado sin interactuar mientras carga.',
                ],
            ],
            [
                'key' => 'objections',
                'number' => 5,
                'name' => 'Objeciones con empatia',
                'description' => 'Ante quejas o dudas del cliente, el playero ofrece soluciones claras con empatia y persuasion.',
                'good_examples' => [
                    'Entiendo que el precio subio, pero Infinia le rinde mas kilometros por litro.',
                    'Si, la espera fue larga, le pido disculpas. Ya lo atiendo enseguida.',
                ],
                'bad_examples' => [
                    'Ignorar las quejas del cliente.',
                    'Ponerse a la defensiva o discutir con el cliente.',
                ],
            ],
            [
                'key' => 'strategic_questions',
                'number' => 6,
                'name' => 'Preguntas estrategicas',
                'description' => 'El playero hace preguntas relevantes segun el tipo de cliente para entender mejor sus necesidades.',
                'good_examples' => [
                    'Hace viaje largo? Porque si es asi le conviene revisar los fluidos.',
                    'Cuanto necesita cargar? Tanque lleno o un monto?',
                ],
                'bad_examples' => [
                    'No preguntar nada y asumir lo que el cliente quiere.',
                    'Hacer preguntas irrelevantes que no ayudan.',
                ],
            ],
            [
                'key' => 'cross_selling',
                'number' => 7,
                'name' => 'Venta cruzada',
                'description' => 'El playero ofrece servicios complementarios: control de fluidos, cafe, informacion de ruta, productos Full.',
                'good_examples' => [
                    'Quiere un cafe de la tienda Full mientras espera?',
                    'Aprovecho y le reviso el aceite y el agua del radiador?',
                ],
                'bad_examples' => [
                    'No ofrecer ningun producto o servicio adicional.',
                ],
            ],
            [
                'key' => 'upselling',
                'number' => 8,
                'name' => 'Venta adicional (producto superior)',
                'description' => 'El playero sugiere un combustible superior: Infinia si pide Super, Infinia Diesel si pide D500, o sugiere apertura de capot.',
                'good_examples' => [
                    'En vez de Super, no quiere probar Infinia? Le cuida mas el motor.',
                    'Si carga Infinia Diesel en vez de D500, va a notar mejor rendimiento.',
                ],
                'bad_examples' => [
                    'No sugerir ninguna mejora de producto.',
                    'Presionar excesivamente al cliente para que compre algo mas caro.',
                ],
            ],
            [
                'key' => 'payment_methods',
                'number' => 9,
                'name' => 'Medios de pago',
                'description' => 'El playero menciona beneficios bancarios, descuentos con tarjetas, o posiciona la app YPF.',
                'good_examples' => [
                    'Si paga con la app YPF tiene descuento.',
                    'Con tarjeta Visa tiene 10% de descuento esta semana.',
                ],
                'bad_examples' => [
                    'No mencionar ninguna opcion de pago beneficiosa.',
                    'Decir solo "efectivo o tarjeta?" sin informar beneficios.',
                ],
            ],
            [
                'key' => 'communication_style',
                'number' => 10,
                'name' => 'Comunicacion adecuada al arquetipo',
                'description' => 'El playero adapta su estilo de comunicacion segun el tipo de cliente (apurado: rapido y directo; enojado: calma y empatia; etc.).',
                'good_examples' => [
                    'Con cliente apurado: ser breve y eficiente en las respuestas.',
                    'Con cliente enojado: mantener la calma, validar su frustracion.',
                ],
                'bad_examples' => [
                    'Hablar lento y dar explicaciones largas a un cliente apurado.',
                    'Responder de forma cortante a un cliente amable.',
                ],
            ],
            [
                'key' => 'discounts_promos',
                'number' => 11,
                'name' => 'Descuentos y promociones',
                'description' => 'El playero menciona promociones semanales, descuentos vigentes u ofertas especiales.',
                'good_examples' => [
                    'Hoy tenemos 15% de descuento con tarjeta Nacion.',
                    'Esta semana hay promo 2x1 en cafes de la tienda Full.',
                ],
                'bad_examples' => [
                    'No mencionar ninguna promocion durante toda la conversacion.',
                ],
            ],
            [
                'key' => 'wow_effect',
                'number' => 12,
                'name' => 'Efecto WOW',
                'description' => 'El playero genera sorpresa o valor agregado inesperado que mejora la experiencia del cliente.',
                'good_examples' => [
                    'Le dejo un ambientador de regalo para el auto.',
                    'Le llene el liquido limpiaparabrisas sin cargo, cortesia de la estacion.',
                ],
                'bad_examples' => [
                    'Interaccion completamente mecanica sin ningun detalle especial.',
                ],
            ],
            [
                'key' => 'farewell',
                'number' => 13,
                'name' => 'Despedida amable',
                'description' => 'El playero se despide cordialmente, deseando buen viaje, invitando a volver, y/o usando el nombre del cliente.',
                'good_examples' => [
                    'Que tenga buen viaje! Lo esperamos pronto.',
                    'Muchas gracias por venir, que le vaya bien!',
                ],
                'bad_examples' => [
                    'Chau. (seco, sin calidez)',
                    'No despedirse.',
                ],
            ],
        ];
    }

    public static function keys(): array
    {
        return array_column(self::all(), 'key');
    }

    public static function buildPromptSection(): string
    {
        $lines = [];

        foreach (self::all() as $criterion) {
            $lines[] = "## Criterio {$criterion['number']}: {$criterion['name']}";
            $lines[] = "Key: {$criterion['key']}";
            $lines[] = "Que se evalua: {$criterion['description']}";
            $lines[] = "Ejemplos BIEN: " . implode(' | ', $criterion['good_examples']);
            $lines[] = "Ejemplos MAL: " . implode(' | ', $criterion['bad_examples']);
            $lines[] = '';
        }

        return implode("\n", $lines);
    }
}
