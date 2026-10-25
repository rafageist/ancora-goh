-- Access-request portal (R0) schema, in the control-plane database
-- timetables_control (docs/saas/adr-001-tenant-isolation.md). Version 1.
--
-- Run as the owner role (tt_control_owner) by `bin/portal migrate`. The
-- runtime role (tt_control_app) gets DML on these tables only (grants.sql).
-- Applicant data is minimal and is purged by `bin/portal purge` according to
-- the privacy notice. request_event keeps no personal data.

CREATE SCHEMA IF NOT EXISTS portal;

CREATE TABLE portal.schema_version (
    version    integer PRIMARY KEY,
    applied_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE portal.access_request (
    id                uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    status            text NOT NULL CHECK (status IN (
                          'submitted', 'email_verification_pending', 'pending_review',
                          'approved', 'rejected', 'more_information_requested',
                          'expired', 'cancelled', 'suspended')),
    full_name         text NOT NULL CHECK (char_length(full_name) BETWEEN 2 AND 120),
    email             text NOT NULL CHECK (char_length(email) BETWEEN 3 AND 254),
    email_normalized  text NOT NULL,
    organization      text NOT NULL CHECK (char_length(organization) BETWEEN 2 AND 160),
    organization_type text NOT NULL CHECK (organization_type IN ('institution', 'independent')),
    country           char(2) NOT NULL CHECK (country ~ '^[A-Z]{2}$'),
    role              text NOT NULL,
    intended_use      text NOT NULL CHECK (char_length(intended_use) BETWEEN 20 AND 1000),
    website           text CHECK (website IS NULL OR char_length(website) <= 200),
    privacy_version   text NOT NULL,
    created_at        timestamptz NOT NULL DEFAULT now(),
    updated_at        timestamptz NOT NULL DEFAULT now(),
    email_sent_at     timestamptz,
    email_verified_at timestamptz,
    decided_at        timestamptz
);
CREATE INDEX access_request_email_idx ON portal.access_request (email_normalized);
CREATE INDEX access_request_status_idx ON portal.access_request (status, created_at);

-- single-use verification tokens; only the SHA-256 of the token is stored
CREATE TABLE portal.verification_token (
    token_hash bytea PRIMARY KEY CHECK (octet_length(token_hash) = 32),
    request_id uuid NOT NULL REFERENCES portal.access_request (id) ON DELETE CASCADE,
    created_at timestamptz NOT NULL DEFAULT now(),
    expires_at timestamptz NOT NULL,
    used_at    timestamptz
);
CREATE INDEX verification_token_request_idx ON portal.verification_token (request_id);

-- audit trail of every state change and operator decision; no personal data,
-- kept after the request itself is purged
CREATE TABLE portal.request_event (
    id          bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    request_id  uuid NOT NULL,
    at          timestamptz NOT NULL DEFAULT now(),
    actor_type  text NOT NULL CHECK (actor_type IN ('applicant', 'operator', 'system')),
    actor       text NOT NULL,
    from_status text,
    to_status   text NOT NULL,
    reason      text
);
CREATE INDEX request_event_request_idx ON portal.request_event (request_id, id);

-- fixed-window rate limits; bucket keys are HMACs, never raw IPs or emails
CREATE TABLE portal.rate_limit (
    bucket       text NOT NULL,
    window_start timestamptz NOT NULL,
    hits         integer NOT NULL,
    PRIMARY KEY (bucket, window_start)
);

INSERT INTO portal.schema_version (version) VALUES (1);
