export type ParticipantPaymentSummary = {
  status: string;
  has_proof: boolean;
};

export type ParticipantPayment = {
  id: number;
  amount: number;
  amount_label: string;
  status: string;
  has_proof: boolean;
  created_at: string | null;
  created_at_label: string | null;
  verified_at: string | null;
  verified_at_label: string | null;
  verifier: string | null;
  verification_notes: string | null;
};

export type ParticipantEventSummary = {
  id: number;
  name: string;
  event_date: string | null;
  event_date_label: string | null;
  location: string | null;
  price: number;
  price_label: string;
};

export type ParticipantEventDetail = ParticipantEventSummary & {
  organizer: string | null;
  event_start_label: string | null;
  event_end_label: string | null;
};

export type ParticipantRegistration = {
  id: number;
  registration_number: string;
  status: string;
  registered_at: string | null;
  registered_at_label: string | null;
  created_at: string | null;
  average_score: number;
  event: ParticipantEventSummary | null;
  payment: ParticipantPaymentSummary | null;
};

export type ParticipantJuryScore = {
  jury: string | null;
  score: number;
  score_label: string;
  public_feedback: string | null;
  created_at_label: string | null;
};

export type ParticipantRegistrationDetail = Omit<ParticipantRegistration, "event" | "payment"> & {
  notes: string | null;
  team_name?: string | null;
  team_members?: { name: string; email?: string | null }[] | null;
  registered_at_time_label: string | null;
  event: ParticipantEventDetail | null;
  user: { name: string | null; email: string | null };
  payment: ParticipantPayment | null;
  response: Record<string, string | null>;
  files?: { id: number; label: string; name: string; size: number; url: string }[];
  jury_scores: ParticipantJuryScore[];
};

export type RegistrationListResponse = {
  registrations: ParticipantRegistration[];
};

export type RegistrationDetailResponse = {
  registration: ParticipantRegistrationDetail;
};

export type PaymentFormResponse = {
  registration: {
    id: number;
    registration_number: string;
    status: string;
    event: {
      id: number;
      name: string;
      price: number;
      price_label: string;
    };
    amount: number;
    amount_label: string;
  };
};
