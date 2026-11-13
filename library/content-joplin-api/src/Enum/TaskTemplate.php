<?php

declare(strict_types=1);

namespace App\Enum;

enum TaskTemplate: string
{
    case EMPTY = 'empty';
    case MEETING = 'meeting';
    case CALL = 'call';
    case FOLLOW_UP = 'follow-up';

    public function getBody(): string
    {
        return match ($this) {
            self::EMPTY => '',
            self::MEETING => "📅 Date: \n📍 Location: \n👥 Attendees: \n📝 Agenda: \n\n✅ Action items:",
            self::CALL => "📞 Call with: \n📅 Date: \n📝 Notes: \n\n✅ Action items:",
            self::FOLLOW_UP => "📧 Follow up with: \n📅 Due date: \n📝 Context: \n\n✅ Action items:",
        };
    }
}
