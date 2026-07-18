<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case CheckinSent = 'checkin_sent';
    case Partial = 'partial';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}

enum ReservationSource: string
{
    case Manual = 'manual';
    case Booking = 'booking';
    case Airbnb = 'airbnb';
    case Web = 'web';
    case Pms = 'pms';
    case Other = 'other';
}

enum CheckinType: string
{
    case Online = 'online';
    case Presential = 'presential';
}

enum CheckinStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Verified = 'verified';
    case Rejected = 'rejected';
}

enum TenantStatus: string
{
    case Active = 'active';
    case Trialing = 'trialing';
    case PastDue = 'past_due';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';
}

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Trialing = 'trialing';
    case PastDue = 'past_due';
    case Canceled = 'canceled';
    case Incomplete = 'incomplete';
    case IncompleteExpired = 'incomplete_expired';
}

enum SesSubmissionStatus: string
{
    case Draft = 'draft';
    case Ready = 'ready';
    case Sent = 'sent';
    case PartiallySent = 'partially_sent';
    case Acknowledged = 'acknowledged';
    case Failed = 'failed';
    case Rejected = 'rejected';
}

enum DocumentType: string
{
    case Dni = 'dni';
    case Nie = 'nie';
    case Passport = 'passport';
    case Other = 'other';
}

enum UserRole: string
{
    case Superadmin = 'superadmin';
    case Admin = 'admin';
    case Operator = 'operator';
}

enum IntegrationProvider: string
{
    case Booking = 'booking';
    case Airbnb = 'airbnb';
    case Manual = 'manual';
    case Ics = 'ics';
    case Webhook = 'webhook';
    case Pms = 'pms';
}

enum PropertyType: string
{
    case Apartment = 'apartment';
    case House = 'house';
    case Villa = 'villa';
    case Studio = 'studio';
    case Hotel = 'hotel';
    case Rural = 'rural';
    case Other = 'other';
}
