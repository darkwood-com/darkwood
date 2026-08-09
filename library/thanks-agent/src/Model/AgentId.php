<?php

declare(strict_types=1);

namespace App\Model;

enum AgentId: string
{
    case FetchDocumentation = 'FetchDocumentationAgent';
    case Github = 'GithubAgent';
    case Php = 'PHPAgent';
    case Mozilla = 'MozillaAgent';
    case Cost = 'CostAgent';
    case Summary = 'SummaryAgent';
    case Thanks = 'ThanksAgent';

    public function valueWeight(): int
    {
        return match ($this) {
            self::FetchDocumentation => 25,
            self::Github => 40,
            self::Php => 30,
            self::Mozilla => 20,
            self::Cost => 15,
            self::Summary => 15,
            self::Thanks => 0,
        };
    }

    /**
     * @return list<self>
     */
    public static function usefulAgents(): array
    {
        return [
            self::FetchDocumentation,
            self::Github,
            self::Php,
            self::Mozilla,
            self::Cost,
        ];
    }
}
