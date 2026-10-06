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