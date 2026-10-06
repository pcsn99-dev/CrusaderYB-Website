export interface College {
    id: number;
    college_name: string;
}

export interface StudentAccountFilters {
    search: string;
    collegeId: number | null;
    graduationYear: string;
}