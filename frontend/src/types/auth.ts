export interface AuthUser {
    public_id: string
    name: string
    email: string
    avatar: string | null
}

export interface ResetCredentials {
    token: string
    email: string
}
