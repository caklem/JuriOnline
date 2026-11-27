export type JuryRoleForm = {
  id: number;
  name: string | null;
  use_default_score: boolean;
  criteria_fields: FormField[];
};

export type FormField = {
  label: string;
  type: "score" | "textarea" | "text" | string;
  max?: number;
  required?: boolean;
  description?: string;
};

export type ExistingScore = {
  id: number;
  registration_id: number;
  jury_form_template_id: number | null;
  score: number;
  public_feedback: string | null;
  private_evaluation: string | null;
  criteria_responses: Record<string, unknown> | null;
  created_at_label: string | null;
};

export type EventSummary = {
  id: number;
  name: string;
  status: string;
  status_label: string;
  category: string | null;
  organizer: string | null;
  scoring_open: boolean;
  scoring_mode: "open" | "closed" | "auto";
  scoring_label: string;
  scoring_opens_at_label: string | null;
};

export type EventCard = EventSummary & {
  description_excerpt: string;
  event_date_label: string;
  banner_url: string | null;
  participants_count: number;
  verified_count: number;
  pending_count: number;
  assigned_form: JuryRoleForm | null;
};

export type Meta = {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
};

export type Invitation = {
  id: number;
  status: string;
  status_label: string;
  jury_role: string | null;
  form: string | null;
  organizer?: string | null;
  created_at_label?: string | null;
  responded_at_label?: string | null;
  event: { id: number; name: string } | null;
};

export type InvitationsResponse = {
  pending: Invitation[];
  responded: Invitation[];
};

export type EventsResponse = {
  events: EventCard[];
  meta: Meta;
};

export type ParticipantScore = {
  id: number;
  jury_id: number;
  jury_name: string | null;
  score: number;
};

export type Participant = {
  id: number;
  status: string;
  status_label: string;
  notes: string | null;
  user: { id: number; name: string; email: string } | null;
  average_score: number | null;
  jury_count: number;
  my_score: { id: number; score: number } | null;
  scores: ParticipantScore[];
};

export type ParticipantsResponse = {
  event: EventSummary;
  registrations: Participant[];
  meta: Meta;
  scoring_open: boolean;
  scoring_mode: "open" | "closed" | "auto";
  scoring_opens_at_label: string | null;
  verified_total: number;
  my_scored_total: number;
  progress_percent: number;
  assigned_form: JuryRoleForm | null;
};

export type ScoreFormResponse = {
  event: EventSummary;
  registration: Participant;
  form: JuryRoleForm | null;
  existing_score: ExistingScore | null;
  average_score: number | null;
  scoring_open: boolean;
  scoring_mode: "open" | "closed" | "auto";
  scoring_opens_at_label: string | null;
};

export type LeaderboardEntry = {
  registration_id: number;
  rank: number;
  user: { id: number; name: string; email: string } | null;
  average_score: number;
  jury_count: number;
  scores: {
    id: number;
    jury_id: number;
    jury_name: string | null;
    score: number;
    public_feedback: string | null;
  }[];
};

export type LeaderboardResponse = {
  event: EventSummary;
  registrations: LeaderboardEntry[];
};

export type FormPreviewResponse = {
  event: EventSummary;
  form: JuryRoleForm;
};
