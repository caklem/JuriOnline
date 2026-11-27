export type Role = { id: number; name: string; display_name: string };

export type MeResponse = {
  user: { id: number; name: string; email: string; role: string; role_label: string; event_scope: string | null };
};

export type UserRow = {
  id: number;
  name: string;
  email: string;
  role_id: number | null;
  role: Role | null;
  role_label: string;
  event_scope: "all" | "own" | null;
  is_active: boolean;
  is_admin: boolean;
  is_event_admin: boolean;
  created_at: string | null;
};

export type UsersResponse = {
  data: UserRow[];
  roles: Role[];
  summary: { total: number; active: number; inactive: number; owners: number };
  filters: { q: string; role: string; status: string };
  meta: { current_page: number; last_page: number; per_page: number; total: number };
};

export type ScoreRow = {
  id: number;
  score: number;
  public_feedback: string | null;
  private_evaluation: string | null;
  created_at: string | null;
  event: { id: number | null; name: string | null };
  participant: { id: number | null; name: string | null };
  jury: { id: number | null; name: string | null };
};

export type ScoresResponse = {
  data: ScoreRow[];
  stats: {
    total_scores: number;
    total_events_scored: number;
    average_score: number;
    total_juries: number;
  };
  meta: { current_page: number; last_page: number; per_page: number; total: number };
};
