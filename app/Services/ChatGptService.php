<?php

namespace App\Services;

use App\Http\Services\EpisodesService;
use App\Models\Answer;
use App\Models\Episodes;
use App\Models\Question;
use OpenAI;
use Exception;
use function App\Helpers\wGetConfigs;

class ChatGptService
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(wGetConfigs('chatgpt-api-key', ''));

    }


    public function generateAnswer($messages, $lastMessage)
    {


        $system_prompt = wGetConfigs('chatgpt-system-prompt', '');
        $history = [];
        foreach ($messages as $msg) {
            if ($msg->from) {
                // mensaje entrante del usuario
                $history[] = [
                    'role' => 'user',
                    'content' => $msg->text,
                ];
            } elseif ($msg->to) {
                if($msg->id == $lastMessage->id){
                  continue;
                }
                // mensaje de salida enviado por el bot
                $history[] = [
                    'role' => 'assistant',
                    'content' => $msg->text,
                ];
            }
        }

        // Construir el mensaje para el modelo
        $messages = [
            [
                'role' => 'system',
                'content' => $system_prompt,
            ],
            ...$history,
            [
                'role' => 'user',
                'content' => $lastMessage->text,
            ],
        ];

        $response = $this->client->chat()->create([
            'model' => wGetConfigs('chatgpt-version', 'gpt-3.5-turbo'),
            'messages' => $messages,
            'max_tokens' => (int)wGetConfigs('max-tokens', 500),
        ]);
        $response = $response->choices[0]->message->content ?? null;
        $response = json_decode($response, true);
        $response = (object)$response;
        return $response->message;


    }
}
