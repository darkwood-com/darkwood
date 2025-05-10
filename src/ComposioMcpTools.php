<?php

namespace App;

use PhpMcp\Server\Attributes\McpTool;

class ComposioMcpTools
{
    /**
      * Action to fetch all emails from gmail.
      * @param bool $include_payload Include the payload of the message in the results.
      * @param bool $include_spam_trash Include messages from SPAM and TRASH in the results.
      * @param array $label_ids Filter messages by their label IDs. Labels identify the status or category of messages. Some of the in-built labels include 'INBOX', 'SPAM', 'TRASH', 'UNREAD', 'STARRED', 'IMPORTANT', 'CATEGORY_PERSONAL', 'CATEGORY_SOCIAL', 'CATEGORY_PROMOTIONS', 'CATEGORY_UPDATES', and 'CATEGORY_FORUMS'. The 'label_ids' for custom labels can be found in the response of the 'listLabels' action. Note: The label_ids is a list of label IDs to filter the messages by.
      * @param int $max_results Maximum number of messages to return.
      * @param string $page_token Page token to retrieve a specific page of results in the list. The page token is returned in the response of this action if there are more results to be fetched. If not provided, the first page of results is returned.
      * @param string $query Only return messages matching the specified query.
      * @param string $user_id The user's email address or 'me' for the authenticated user.
      * @return array The response data from the action execution.
      */
    #[McpTool(name: 'gmail_fetch_emails')]
    public function gmailFetchEmails(bool $include_payload = true, bool $include_spam_trash = false, array $label_ids, int $max_results = 1, string $page_token, string $query, string $user_id = 'me'): array
    {
        throw new \Exception("Not implemented");
    }

    /**
      * Fetch messages by message id from gmail.
      * @param string $format The format to return the message in. Possible values: minimal, full, raw, metadata
      * @param string $message_id ID of the message to fetch, you can find the 'message_id' in the response of the 'fetchEmails' action.
      * @param string $user_id The user's email address or 'me' for the authenticated user.
      * @return array The response data from the action execution.
      */
    #[McpTool(name: 'gmail_fetch_message_by_message_id')]
    public function gmailFetchMessageByMessageId(string $format = 'full', string $message_id, string $user_id = 'me'): array
    {
        throw new \Exception("Not implemented");
    }

    /**
      * Fetch messages by thread id from gmail with pagination support. to use pagination, you can set the 'pagetoken' in the request to the value of the 'nextpagetoken' in the response of the previous action. the 'nextpagetoken' is returned in the response of this action (i.e 'fetchmessagebythreadid') if there are more results to be fetched. if not provided, the first page of results is returned.
      * @param string $page_token Page token to retrieve a specific page of results in the list. The next_page_token is returned in the response of this action (i.e 'fetchMessageByThreadId') if there are more results to be fetched. If not provided, the first page of results is returned.
      * @param string $thread_id ID of the thread containing the message to fetch, you can find the 'thread_id' in the response of the 'fetchEmails' action or 'listThreads' actions.
      * @param string $user_id The user's email address or 'me' for the authenticated user.
      * @return array The response data from the action execution.
      */
    #[McpTool(name: 'gmail_fetch_message_by_thread_id')]
    public function gmailFetchMessageByThreadId(string $page_token = '', string $thread_id, string $user_id = 'me'): array
    {
        throw new \Exception("Not implemented");
    }

    /**
      * Send an email using gmail's api.
      * @param array $attachment Path of File to be attached with the mail, If attachment to be sent.
      * @param array $bcc Email addresses of the recipients to be added as a blind carbon copy (BCC).
      * @param string $body Body content of the email. Can be plain text or HTML.
      * @param array $cc Email addresses of the recipients to be added as a carbon copy (CC).
      * @param array $extra_recipients Extra email addresses of the recipients to be added.These will be treated as regular recipients, not CC or BCC.
      * @param bool $is_html Set to True if the body content is HTML.
      * @param string $recipient_email Email address of the recipient
      * @param string $subject Subject of the email
      * @param string $user_id The user's email address or 'me' for the authenticated user.
      * @return array The response data from the action execution.
      */
    #[McpTool(name: 'gmail_send_email')]
    public function gmailSendEmail(array $attachment, array $bcc = array (
), string $body, array $cc = array (
), array $extra_recipients = array (
), bool $is_html = false, string $recipient_email, string $subject, string $user_id = 'me'): array
    {
        throw new \Exception("Not implemented");
    }

    /**
      * Create a new event in a google calendar.
      * @param array $attendees List of attendee emails (strings).
      * @param string $calendar_id The ID of the Google Calendar. `primary` for interacting with the primary calendar.
      * @param bool $create_meeting_room If true, a Google Meet link is created and added to the event.
      * @param string $description Description of the event. Can contain HTML. Optional.
      * @param string $eventType Type of the event, immutable post-creation. Currently, only 'default' and 'workingLocation' can be created.
      * @param int $event_duration_hour Number of hours (0-24). Increase by 1 here rather than passing 60 in `event_duration_minutes`
      * @param int $event_duration_minutes Number of minutes (0-59). Make absolutely sure this is less than 60.
      * @param bool $guestsCanInviteOthers Whether attendees other than the organizer can invite others to the event.
      * @param bool $guestsCanSeeOtherGuests Whether attendees other than the organizer can see who the event's attendees are.
      * @param bool $guests_can_modify If True, guests can modify the event.
      * @param string $location Geographic location of the event as free-form text.
      * @param array $recurrence List of RRULE, EXRULE, RDATE, EXDATE lines for recurring events.
      * @param bool $send_updates Defaults to True. Whether to send updates to the attendees.
      * @param string $start_datetime Naive date/time (YYYY-MM-DDTHH:MM:SS) with NO offsets or Z. e.g. '2025-01-16T13:00:00'
      * @param string $summary Summary (title) of the event.
      * @param string $timezone IANA timezone name (e.g., 'America/New_York'). Required if datetime is naive. If datetime includes timezone info (Z or offset), this field is optional and defaults to UTC.
      * @param string $transparency 'opaque' (busy) or 'transparent' (available).
      * @param string $visibility Event visibility: 'default', 'public', 'private', or 'confidential'.
      * @return array The response data from the action execution.
      */
    #[McpTool(name: 'googlecalendar_create_event')]
    public function googlecalendarCreateEvent(array $attendees, string $calendar_id = 'primary', bool $create_meeting_room, string $description, string $eventType = 'default', int $event_duration_hour = 0, int $event_duration_minutes = 30, bool $guestsCanInviteOthers, bool $guestsCanSeeOtherGuests, bool $guests_can_modify = false, string $location, array $recurrence, bool $send_updates, string $start_datetime, string $summary, string $timezone, string $transparency = 'opaque', string $visibility = 'default'): array
    {
        throw new \Exception("Not implemented");
    }

    /**
      * Find events in a google calendar based on a search query.
      * @param string $calendar_id Identifier of the Google Calendar. Use 'primary' for the currently logged in user's primary calendar.
      * @param array $event_types List of event types to return. Possible values are: default, outOfOffice, focusTime, workingLocation.
      * @param int $max_results Maximum number of events returned on one result page. The page size can never be larger than 2500 events. The default value is 10.
      * @param string $order_by The order of the events returned in the result. Acceptable values are 'startTime' and 'updated'.
      * @param string $page_token Token specifying which result page to return. Optional.
      * @param string $query Search term to find events that match these terms in the event's summary, description, location, attendee's displayName, attendee's email, organizer's displayName, organizer's email, etc if needed.
      * @param bool $show_deleted Whether to include deleted events (with status equals 'cancelled') in the result.
      * @param bool $single_events Whether to expand recurring events into instances and only return single one-off events and instances of recurring events, but not the underlying recurring events themselves.
      * @param string $timeMax Upper bound (exclusive) for an event's start time to filter by. Accepts multiple formats:
1. ISO format with timezone (e.g., 2024-12-06T13:00:00Z)
2. Comma-separated format (e.g., 2024,12,06,13,00,00)
3. Simple datetime format (e.g., 2024-12-06 13:00:00)
      * @param string $timeMin Lower bound (exclusive) for an event's end time to filter by. Accepts multiple formats:
1. ISO format with timezone (e.g., 2024-12-06T13:00:00Z)
2. Comma-separated format (e.g., 2024,12,06,13,00,00)
3. Simple datetime format (e.g., 2024-12-06 13:00:00)
      * @param string $updated_min Lower bound for an event's last modification time to filter by. Accepts multiple formats:
1. ISO format with timezone (e.g., 2024-12-06T13:00:00Z)
2. Comma-separated format (e.g., 2024,12,06,13,00,00)
3. Simple datetime format (e.g., 2024-12-06 13:00:00)
      * @return array The response data from the action execution.
      */
    #[McpTool(name: 'googlecalendar_find_event')]
    public function googlecalendarFindEvent(string $calendar_id = 'primary', array $event_types = array (
  0 => 'default',
  1 => 'outOfOffice',
  2 => 'focusTime',
  3 => 'workingLocation',
), int $max_results = 10, string $order_by, string $page_token, string $query, bool $show_deleted, bool $single_events = true, string $timeMax, string $timeMin, string $updated_min): array
    {
        throw new \Exception("Not implemented");
    }

    /**
      * Find free slots in a google calendar based on for a specific time period.
      * @param int $calendar_expansion_max Maximal number of calendars for which FreeBusy information is to be provided. Optional. Maximum value is 50.
      * @param int $group_expansion_max Maximal number of calendar identifiers to be provided for a single group. Optional. An error is returned for a group with more members than this value. Maximum value is 100.
      * @param array $items List of calendars ids for which to fetch
      * @param string $time_max The end datetime of the interval for the query. Supports multiple formats:
1. ISO format with timezone (e.g., 2024-12-06T13:00:00Z)
2. Comma-separated format (e.g., 2024,12,06,13,00,00)
3. Simple datetime format (e.g., 2024-12-06 13:00:00)
      * @param string $time_min The start datetime of the interval for the query. Supports multiple formats:
1. ISO format with timezone (e.g., 2024-12-06T13:00:00Z)
2. Comma-separated format (e.g., 2024,12,06,13,00,00)
3. Simple datetime format (e.g., 2024-12-06 13:00:00)
      * @param string $timezone Time zone used in the response. Optional. The default is UTC.
      * @return array The response data from the action execution.
      */
    #[McpTool(name: 'googlecalendar_find_free_slots')]
    public function googlecalendarFindFreeSlots(int $calendar_expansion_max = 50, int $group_expansion_max = 100, array $items = array (
  0 => 'primary',
), string $time_max, string $time_min, string $timezone = 'UTC'): array
    {
        throw new \Exception("Not implemented");
    }

    /**
      * Action to fetch a calendar based on the provided calendar id.
      * @param string $calendar_id The ID of the Google Calendar that needs to be fetched. Default is 'primary'.
      * @return array The response data from the action execution.
      */
    #[McpTool(name: 'googlecalendar_get_calendar')]
    public function googlecalendarGetCalendar(string $calendar_id = 'primary'): array
    {
        throw new \Exception("Not implemented");
    }

    /**
      * Action to get the current date and time of a specified timezone, given its utc offset value.
      * @param mixed $timezone The timezone offset from UTC to retrieve current date and time, like for location of UTC+6, you give 6, for UTC -9, your give -9.
      * @return array The response data from the action execution.
      */
    #[McpTool(name: 'googlecalendar_get_current_date_time')]
    public function googlecalendarGetCurrentDateTime(mixed $timezone = 0): array
    {
        throw new \Exception("Not implemented");
    }

    /**
      * Action to list all google calendars from the user's calendar list with pagination.
      * @param int $max_results Maximum number of entries returned on one result page. The page size can never be larger than 250 entries.
      * @param string $min_access_role The minimum access role for the user in the returned entries.
      * @param string $page_token Token specifying which result page to return.
      * @param bool $show_deleted Whether to include deleted calendar list entries in the result.
      * @param bool $show_hidden Whether to show hidden entries.
      * @param string $sync_token Token obtained from the nextSyncToken field returned on the last page of results from the previous list request.
      * @return array The response data from the action execution.
      */
    #[McpTool(name: 'googlecalendar_list_calendars')]
    public function googlecalendarListCalendars(int $max_results = 10, string $min_access_role, string $page_token, bool $show_deleted = false, bool $show_hidden = false, string $sync_token): array
    {
        throw new \Exception("Not implemented");
    }

    /**
      * Create a new event in a google calendar based on a simple text string like 'appointment at somewhere on june 3rd 10am-10:25am' you can only give title and timeslot here. no recurring meetings and no attendee can be added here. this is not a preferred endpoint. only use this if no other endpoint is possible.
      * @param string $calendar_id Calendar identifier. To list calendars to retrieve calendar IDs use relevant tools. To access the primary calendar of the currently logged in user, use the 'primary' keyword.
      * @param string $send_updates Guests who should receive notifications about the creation of the new event.
      * @param string $text The text describing the event to be created.
      * @return array The response data from the action execution.
      */
    #[McpTool(name: 'googlecalendar_quick_add')]
    public function googlecalendarQuickAdd(string $calendar_id = 'primary', string $send_updates = 'none', string $text = ''): array
    {
        throw new \Exception("Not implemented");
    }
}
