export interface College {
    id: number;
    college_name: string;
}

export interface StudentAccountFilters {
    search: string;
    collegeId: number | null;
    graduationYear: string;
}

export interface StudentSummary {
    id: number;
    university_id: string;
    full_name: string;
    college: string | null;
    program: string | null;
    major: string | null;
    year: string | null;
    is_subscribe: boolean;
    is_third_party: boolean;
}

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

export interface StudentSearchResponse {
    data: StudentSummary[];
    meta: PaginationMeta;
}

export type AttendanceStatus =
    | 'present'
    | 'absent'
    | 'late'
    | 'not-recorded';

export interface StudentPictorial {
    id: number;
    date: string;
    start_time: string;
    end_time: string;
}

export interface StudentReservation {
    id: number;
    is_rescheduled: boolean;
    reschedule_date: string | null;
    is_present_date: string | null;
    attendance_status: AttendanceStatus;
    created_at: string | null;
    pictorial: StudentPictorial | null;
}

export interface StudentAccountDetail {
    id: number;

    university_id: string;
    slmis_id: number | null;

    first_name: string;
    middle_name: string | null;
    last_name: string;
    suffix: string | null;
    full_name: string;

    email: string | null;
    contact_number: string | null;
    current_address: string | null;
    permanent_address: string | null;

    graduation_year: string | null;
    expected_graduation_date: string | null;

    college: string | null;
    program: string | null;
    major: string | null;

    is_agree_contract: boolean;
    is_subscribe: boolean;
    is_third_party: boolean;

    subscribe_date: string | null;
    unsubscribe_date: string | null;

    claim_pic: boolean;
    claim_pic_date: string | null;

    reservations: StudentReservation[];
}