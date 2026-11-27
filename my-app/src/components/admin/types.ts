export type ApiMeta = {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
};

export type CategoryOption = { id: number; name: string };

export type EventSummary = {
  id: number;
  name: string;
  slug: string | null;
  public_url: string | null;
  description: string | null;
  format?: string;
  participation_mode?: string;
  team_min_members?: number;
  team_max_members?: number;
  timezone?: string;
  contact_name?: string | null;
  contact_email?: string | null;
  contact_phone?: string | null;
  archived_at?: string | null;
  status: string;
  status_label: string;
  price: number;
  quota: number;
  registered_count: number;
  available_slots: number;
  is_full: boolean;
  location: string | null;
  banner: string | null;
  banner_url: string | null;
  juknis_file?: string | null;
  juknis_name?: string | null;
  juknis_url?: string | null;
  registration_start: string | null;
  registration_end: string | null;
  event_date: string | null;
  event_start: string | null;
  event_end: string | null;
  scoring_mode: "open" | "closed" | "auto";
  scoring_open: boolean;
  scoring_status_label: string;
  scoring_opens_at: string | null;
  organizer: { id: number; name: string } | null;
  category: { id: number; name: string } | null;
  registrations_count: number;
  registration_open: boolean;
};

export type PaymentRow = {
  id: number;
  amount: number;
  status: string;
  status_label: string;
  verification_notes: string | null;
  verified_at: string | null;
  created_at: string | null;
  has_proof: boolean;
  verifier: { id: number; name: string } | null;
  registration: {
    id: number;
    registration_number: string;
    user: { id: number; name: string } | null;
    event: { id: number; name: string } | null;
  } | null;
};

export type ParticipantRow = {
  id: number;
  registration_number: string;
  status: string;
  status_label: string;
  notes: string | null;
  registered_at: string | null;
  user: { id: number; name: string; email: string } | null;
  payment: { id: number; status: string; status_label: string } | null;
  average_score: number;
  scores_count: number;
};

export type JuryOption = {
  id: number;
  name: string;
  email: string;
  is_active: boolean;
};

export type FormTemplateRow = {
  id: number;
  name: string;
  criteria_count: number;
  criteria_fields: { label?: string; type?: string; max?: number; required?: boolean }[];
};

export type AssignmentRow = {
  id: number;
  jury: { id: number | null; name: string | null; email: string | null; is_active: boolean };
  jury_role: string | null;
  status: string;
  status_label: string;
  form_template_id: number | null;
  form_template_name: string | null;
  created_at: string | null;
  responded_at: string | null;
  can_reset_password?: boolean;
};

export type RecapRow = {
  registration_number: string;
  participant_name: string | null;
  address: string;
  form_details: { id: number; name: string; score: number }[];
  total_score: number;
  average_score: number;
  legacy_score: number;
  jury_count: number;
  form_count: number;
  rank: number;
};
