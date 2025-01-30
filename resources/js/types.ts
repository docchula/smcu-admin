export interface Activity {
    id: number;
    name: string;
    organization: string;
    period_start: string;
    period_end: string | null;
    duration: number | null;
    role: string;
    description: string | null;
    attachment: string | null;
}


export interface ActivityParticipant {
    name: string;
    student_id: string;
    nickname?: string;
    id?: string;
    type: string;
    title: string | null;
}

export interface Document {
    id: number;
    created_at: string | null;
    updated_at: string | null;
    year: number;
    number: number;
    number_to: number | null;
    title: string;
    recipient: string | null;
    user_id: number | null;
    project_id: number | null;
    department_id: number | null;
    attachment_path: string | null;
    tag: string | null;
    approved_path: string | null;
    status: string | null;
}

export interface Personnel {
    id: number;
    year: number;
    department_id: number | null;
    email: string | null;
    name: string;
    name_en: string | null;
    position: string;
    position_en: string | null;
    supervisor: number | null;
    sequence: number;
    photo_path: string | null;
    created_at: string | null;
    updated_at: string | null;
}

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

export interface TranscriptItem {
    identifier: string;
    project_id?: number;
    activity_id?: number;
    name: string;
    department: string;
    period_start: string;
    period_end: string | null;
    duration: number | null;
    role: string;
    approve_status: number;
    title: string | null;
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
