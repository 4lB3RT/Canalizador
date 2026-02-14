<?php

declare(strict_types=1);

namespace Canalizador\Script\Infrastructure\Repositories\OpenAI;

use Canalizador\Channel\Domain\Entities\Channel;
use Canalizador\Script\Domain\Repositories\ScriptIdeaGenerator;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

final class OpenAIScriptIdeaGenerator implements ScriptIdeaGenerator
{
    public function generateIdea(Channel $channel): string
    {
        return $this->generateAstrology($channel);
    }

    public function generateGaming(Channel $channel): string
    {
        $systemPrompt = config('prompts.script.idea_generator_gaming.system_prompt');

        $channelInfo = $this->extractChannelInfo($channel);
        $systemPrompt = str_replace(
            ['{channel_thumbnail_url}', '{channel_name}', '{channel_description}'],
            [$channelInfo['thumbnail_url'], $channelInfo['name'], $channelInfo['description']],
            $systemPrompt
        );

        $response = Prism::text()
            ->using(Provider::OpenAI, config('openai.model'))
            ->withSystemPrompt($systemPrompt)
            ->withPrompt('Genera una idea creativa y original para un guion de vídeo corto de gaming.')
            ->asText();

        return trim($response->text);
    }

    public function generateAstrology(Channel $channel): string
    {
        $systemPrompt = config('prompts.script.idea_generator_astrology.system_prompt');

        $channelInfo = $this->extractChannelInfo($channel);
        $systemPrompt = str_replace(
            ['{channel_name}', '{channel_description}'],
            [$channelInfo['name'], $channelInfo['description']],
            $systemPrompt
        );

        $response = Prism::text()
            ->using(Provider::OpenAI, config('openai.model'))
            ->withSystemPrompt($systemPrompt)
            ->withPrompt('Genera una idea creativa y original para un guion de vídeo corto de astrología.')
            ->asText();

        return trim($response->text);
    }

    private function extractChannelInfo(Channel $channel): array
    {
        return [
            'thumbnail_url' => $channel->thumbnailUrl()?->value() ?? '',
            'name' => $channel->title()->value(),
            'description' => $channel->description()->value(),
            'language' => $channel->country()->toLanguageCode(),
        ];
    }
}
