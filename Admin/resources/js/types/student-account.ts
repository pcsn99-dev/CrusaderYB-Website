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
    year: string | null;
    is_rescheduled: boolean;
    reschedule_date: string | null;
    is_present_date: string | null;
    attendance_status: AttendanceStatus;
    created_at: string | null;
    pictorial: StudentPictorial | null;
    cancelled_at: string | null;
    cancellation_reason: string | null;
}

export interface AvailablePictorialSchedule {
    id: number;

    date: string | null;
    date_label: string | null;

    start_time: string | null;
    end_time: string | null;
    time_label: string | null;

    no_of_slots: number;
    reserved_slots: number;
    available_slots: number;

    is_available: boolean;
    is_delayed: boolean;

    delayed_penalty:
        | {
            penalty_type: string;
            label: string;
            amount: number;
        }
        | null;

    is_current_reservation: boolean;
}

export interface CurrentStudentReservation {
    id: number;
    pictorial_id: number;

    date: string | null;
    date_label: string | null;
    time_label: string | null;
}

export interface ReschedulePenaltyPreview {
    penalty_type: string;
    label: string;
    amount: number;
    days_before: number;
}

export interface ReservationAvailabilityResponse {
    data: {
        student: {
            id: number;
            name: string;
            year: string | null;
        };

        current_reservation:
            | CurrentStudentReservation
            | null;

        reschedule_penalty:
            | ReschedulePenaltyPreview
            | null;

        schedules:
            AvailablePictorialSchedule[];
    };
}

export interface StoreStudentReservationResponse {
    message: string;

    data: {
        reservation_id: number;
        was_rescheduled: boolean;

        penalty:
            | ReschedulePenaltyPreview
            | null;

        penalty_waived: boolean;

        delayed_penalty:
            | {
                id: number;
                penalty_type: string;
                label: string;
                amount: number;
            }
            | null;
    };
}

export interface StudentPenaltyReservation {
    id: number;
    date: string | null;
    start_time: string | null;
    end_time: string | null;
}

export interface SubscriptionPackage {
    id: number;
    name: string;
    description: string | null;
    amount: number;
}

export interface StudentPenalty {
    id: number;
    year: string | null;
    penalty_type: string;
    penalty_type_label: string;
    amount: number;

    source:
        | 'attendance'
        | 'manual';

    source_label: string;

    reservation:
        | StudentPenaltyReservation
        | null;
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

    subscription_package_id: number | null;
    subscription_package: SubscriptionPackage | null;

    subscribe_date: string | null;
    unsubscribe_date: string | null;

    claim_pic: boolean;
    claim_pic_date: string | null;

    reservations: StudentReservation[];

    penalties: StudentPenalty[];

    permissions: StudentAccountPermissions;
}

export interface StudentAccountPermissions {
    manage_subscription: boolean;
    manage_third_party: boolean;
    manage_reservations: boolean;
}