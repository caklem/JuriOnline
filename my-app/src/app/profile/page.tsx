import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { PageHeader } from "@/components/kit/ui";
import { ProfileForms } from "./profile-forms";
import { JuryProfileForm } from "./jury-profile-form";

export const metadata = { title: "Profil Saya — JURI.ON" };
export const instant = false;

type ProfileResponse = {
  user: {
    name: string;
    email: string;
    role_label: string;
    jury_profile: {
      phone: string | null;
      organization: string | null;
      expertise: string | null;
      bio: string | null;
      whatsapp_number: string | null;
      whatsapp_consent: boolean;
    } | null;
  };
};

export default async function ProfilePage() {
  const data = await apiFetch<ProfileResponse>("/profile");

  return (
    <AppFrame>
      <PageHeader
        eyebrow="Akun"
        title="Profil Saya"
        description="Kelola informasi akun, kata sandi, dan keamanan akun JURI.ON Anda."
      />
      <p className="mt-2 text-xs text-[var(--text-secondary)]">
        Masuk sebagai {data.user.role_label} · {data.user.email}
      </p>
      <ProfileForms name={data.user.name} email={data.user.email} />
      {data.user.jury_profile ? <JuryProfileForm profile={data.user.jury_profile} /> : null}
    </AppFrame>
  );
}
