<?php
function corrigirRedacaoComIA(string $textoRedacao): array
{
    $apiKey = 'AIzaSyAv7mEGDqpsvvYPz8fml9MPkvjkuMed-BQ';

    if (empty($apiKey)) {
        throw new Exception("Chave da API Gemini não configurada.");
    }

    $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent';

    $prompt = "
Você é um corretor oficial do ENEM.

Avalie a redação a seguir e retorne APENAS um JSON com a estrutura:

{
  \"nota_geral\": (inteiro de 0 a 1000, múltiplo de 40),
  \"competencias\": {
    \"C1\": { \"nota\": (0 a 200), \"comentario\": \"...\" },
    \"C2\": { \"nota\": (0 a 200), \"comentario\": \"...\" },
    \"C3\": { \"nota\": (0 a 200), \"comentario\": \"...\" },
    \"C4\": { \"nota\": (0 a 200), \"comentario\": \"...\" },
    \"C5\": { \"nota\": (0 a 200), \"comentario\": \"...\" }
  },
  \"sugestoes\": [
    \"Sugestão 1\",
    \"Sugestão 2\"
  ]
}

- NÃO inclua explicações fora do JSON.
- Os comentários devem ser curtos, objetivos e em português do Brasil.
- Considere as competências oficiais do ENEM (C1 a C5).

Redação:
\"\"\"$textoRedacao\"\"\"";

    $body = [
        "contents" => [[
            "role" => "user",
            "parts" => [
                ["text" => $prompt]
            ]
        ]],
        "generation_config" => [
            "response_mime_type" => "application/json"
        ]
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            "x-goog-api-key: {$apiKey}"
        ],
        CURLOPT_POSTFIELDS => json_encode($body),
    ]);

    $responseJson = curl_exec($ch);

    if ($responseJson === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("Erro ao conectar à API Gemini: $error");
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode < 200 || $httpCode >= 300) {
        throw new Exception("API Gemini retornou HTTP $httpCode: $responseJson");
    }

    $response = json_decode($responseJson, true);

    if (
        !isset($response['candidates'][0]['content']['parts'][0]['text'])
        || !is_string($response['candidates'][0]['content']['parts'][0]['text'])
    ) {
        throw new Exception("Resposta da IA em formato inesperado.");
    }

    $jsonText = $response['candidates'][0]['content']['parts'][0]['text'];

    $parsed = json_decode($jsonText, true);
    if ($parsed === null) {
        throw new Exception("Não foi possível interpretar o JSON retornado pela IA.");
    }

    return $parsed;
}
