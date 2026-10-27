<?php

declare(strict_types=1);

namespace Ancora\Portal\Domain;

/** Plain-text emails sent to applicants. Subjects never contain applicant data. */
final class Messages
{
    private const SIGNATURE = "\n\n-- \nDivengine Timetables\n"
        . "This message was sent because someone requested access with this address.\n"
        . "If it was not you, ignore it: nothing happens without verification and review.\n";

    /** @return array{0: string, 1: string} subject, body */
    public static function verification(string $name, string $link): array
    {
        return ['Confirm your email for Divengine Timetables',
            "Hello $name,\n\nTo confirm this address for your access request, open the link below"
            . " and press \"Confirm\" within 24 hours:\n\n$link\n\n"
            . "Confirming your email does not create an account. Every request is reviewed by a person;"
            . " you will receive our decision by email." . self::SIGNATURE];
    }

    /** @return array{0: string, 1: string} */
    public static function approved(string $name): array
    {
        return ['Your Divengine Timetables access request was approved',
            "Hello $name,\n\nYour access request was approved. No account has been created yet: when the"
            . " invitation-only beta opens, you will receive a separate invitation to set up your account.\n"
            . self::SIGNATURE];
    }

    /** @return array{0: string, 1: string} */
    public static function rejected(string $name): array
    {
        return ['Your Divengine Timetables access request',
            "Hello $name,\n\nThank you for your interest. We cannot offer access for this request at this time."
            . " Your request data will be deleted according to our privacy notice.\n" . self::SIGNATURE];
    }

    /** @return array{0: string, 1: string} */
    public static function moreInformation(string $name, string $message): array
    {
        return ['More information needed for your Divengine Timetables request',
            "Hello $name,\n\nTo review your access request we need more information:\n\n$message\n\nPlease reply to this email.\n" . self::SIGNATURE];
    }
}
