import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Card, PageHeader } from "@/components/kit/ui";
import { OrganizerProfileForm } from "./organizer-form";

export const metadata = { title: "Profil Organizer — JURI.ON" };
export const instant = false;

type ProfileResponse = {
  profile: {
    organizer_name: string;
    description: string | null;
    contact_email: string | null;
    phone: string | null;
    logo_url: string | null;
    is_complete: boolean;
  };
};

export default async function OrganizerProfilePage() {
  let data: ProfileResponse;

  try {
    data = await apiFetch<ProfileResponse>("/admin/organizer-profile");
  } catch (error) {
    return (
      <AppFrame>
        <PageHeader eyebrow="Organizer" title="Profil Organizer" />
        <Card className="mt-4 p-5 text-sm text-rose-700">
          {error instanceof Error ? error.message : "Profil organizer tidak dapat dimuat."}
        </Card>
      </AppFrame>
    );
  }

  return (
    <AppFrame>
      <PageHeader
        eyebrow="Organizer"
        title="Profil Organizer"
        description="Identitas ini dipakai pada halaman event dan komunikasi dengan peserta. Lengkapi agar event terlihat kredibel."
      />
      <Card className="mt-5 p-5 sm:p-6">
        <OrganizerProfileForm profile={data.profile} />
      </Card>
    </AppFrame>
  );
}
