import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { PageHeader } from "@/components/kit/ui";
import { BackLink } from "@/components/admin/ui";
import { ChatAdmin } from "./chat-admin";

export const metadata = { title: "Chat Event — JURI.ON" };
export const instant = false;

type Response = {
  settings: {
    enabled: boolean;
    participants_can_send: boolean;
    juries_can_send: boolean;
    whatsapp_enabled: boolean;
    whatsapp_group_url: string | null;
    announcement: string | null;
  };
  data: {
    id: number;
    body: string;
    sender: { id: number | null; name: string; role: string | null };
    created_at: string | null;
    reported: boolean;
    report_reason: string | null;
    is_hidden: boolean;
    is_mine: boolean;
  }[];
};

export default async function EventChatAdminPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const data = await apiFetch<Response>(`/admin/events/${id}/chat/messages`);

  return (
    <AppFrame>
      <div className="mb-4"><BackLink href={`/admin/events/${id}`}>Kembali ke detail event</BackLink></div>
      <PageHeader eyebrow="Komunikasi" title="Chat Event" description="Kelola ruang komunikasi peserta, juri, dan panitia. WhatsApp hanya berupa tautan; tidak ada pengiriman otomatis." />
      <div className="mt-5">
        <ChatAdmin eventId={Number(id)} settings={data.settings} messages={data.data} />
      </div>
    </AppFrame>
  );
}
