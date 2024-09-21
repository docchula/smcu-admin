export interface Project {
    id: number;
    created_at: string | null;
    updated_at: string | null;
    year: number;
    number: number;
    name: string;
    advisor: string | null;
    type: string | null;
    recurrence: string | null;
    period_start: string | null;
    period_end: string | null;
    background: string | null;
    aims: string | null;
    outcomes: string | null;
    objectives: string | null;
    expense: string | null;
    user_id: number | null;
    department_id: number | null;
    approval_document_id: number | null;
    duration: number | null;
    estimated_attendees: string | null;
    closure_reminded_at: string | null;
    closure_submitted_at: string | null;
    closure_submitted_by: number | null;
    closure_approved_at: string | null;
    closure_approved_message: string | null;
    closure_approved_by: number | null;
    closure_approved_status: number;
    // Relationship
    participants: ProjectParticipant[];
    user: User;
}

export interface ProjectParticipant {
    id: number;
    created_at: string | null;
    updated_at: string | null;
    user_id: number;
    project_id: number;
    type: 'organizer' | 'staff' | 'attendee';
    title: string | null;
    verify_status: number;
    reject_reason: string | null;
    reject_participants: number[];
    approve_status: number;
    // Relationship
    user: User;
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    password: string;
    remember_token: string | null;
    current_team_id: number | null;
    profile_photo_path: string | null;
    google_id: string | null;
    student_id: string | null;
    created_at: string;
    updated_at: string;
    two_factor_secret: string | null;
    two_factor_recovery_codes: string | null;
    roles: string;
}
