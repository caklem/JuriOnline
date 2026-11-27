import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Card, PageHeader } from "@/components/kit/ui";
import { BackLink } from "@/components/admin/ui";
import { ChatRoom } from "./chat-room";

export const metadata = { title: "Chat Event — JURI.ON" };
export const instant = false;

type Response = {
  event: { id: number; name: string };
  settings: { enabled: boolean; whatsapp_enabled: boolean; whatsapp_group_url: string | null; announcement: string | null };
  access: { can_send: boolean; role: string };
  data: { id: number; body: string; sender: { id: number | null; name: string; role: string | null }; created_at: string | null; is_mine: boolean; reported: boolean }[];
};

export default async function EventChatPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  let data: Response;
  let error: string | null = null;

  try {
    data = await apiFetch<Response>(`/events/${id}/chat`);
  } catch (err) {
    error = err instanceof Error ? err.message : "Chat tidak dapat dimuat.";
    return (
      <AppFrame>
        <BackLink href={`/events/${id}`}>Kembali ke event</BackLink>
        <PageHeader title="Chat Event" />
        <Card className="mt-4 p-5 text-sm text-rose-700">{error}</Card>
      </AppFrame>
    );
  }

  return (
    <AppFrame>
      <div className="mb-4"><BackLink href={`/events/${id}`}>Kembali ke event</BackLink></div>
      <PageHeader eyebrow="Komunikasi" title={`Chat ${data.event.name}`} description="Tanyakan informasi event langsung kepada panitia. Jaga sopan santun; pesan dapat dilaporkan dan dimoderasi." />
      <div className="mt-5">
        <ChatRoom
          eventId={data.event.id}
          messages={data.data}
          canSend={data.access.can_send}
          enabled={data.settings.enabled}
          announcement={data.settings.announcement}
          whatsappUrl={data.settings.whatsapp_enabled ? data.settings.whatsapp_group_url : null}
        />
      </div>
    </AppFrame>
  );
}
