"use client";

import { useActionState } from "react";
import { Button } from "@/components/arc/button/button";
import { Card, CardHeader } from "@/components/kit/ui";
import { sendMessageAction, reportMessageAction, type ChatState } from "./actions";

type Message = {
  id: number;
  body: string;
  sender: { id: number | null; name: string; role: string | null };
  created_at: string | null;
  is_mine: boolean;
  reported: boolean;
};

export function ChatRoom({
  eventId,
  messages,
  canSend,
  enabled,
  announcement,
  whatsappUrl,
}: {
  eventId: number;
  messages: Message[];
  canSend: boolean;
  enabled: boolean;
  announcement: string | null;
  whatsappUrl: string | null;
}) {
  const [sendState, sendAction, sending] = useActionState<ChatState, FormData>(sendMessageAction.bind(null, eventId), {});

  return (
    <div className="space-y-4">
      {announcement ? (
        <div className="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
          <strong>Pengumuman panitia:</strong> {announcement}
        </div>
      ) : null}
      {whatsappUrl ? (
        <a href={whatsappUrl} target="_blank" rel="noopener noreferrer" className="inline-block rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800">
          Gabung Grup WhatsApp Event
        </a>
      ) : null}

      <Card>
        <CardHeader title="Ruang Diskusi" description={enabled ? "Pesan terlihat oleh peserta, juri, dan panitia event ini." : "Chat sedang dinonaktifkan panitia."} />
        <div className="max-h-[520px] space-y-3 overflow-y-auto p-5">
          {messages.length === 0 ? <p className="text-sm text-[var(--text-secondary)]">Belum ada pesan.</p> : messages.map((message) => (
            <div key={message.id} className={`rounded-xl border p-3 ${message.is_mine ? "border-indigo-200 bg-indigo-50/50" : "border-[var(--border)] bg-[var(--background)]"}`}>
              <div className="flex flex-wrap items-center justify-between gap-2">
                <p className="text-xs font-semibold text-[var(--foreground)]">
                  {message.sender.name} <span className="font-normal text-[var(--text-muted)]">({message.sender.role ?? "-"})</span>
                </p>
                {!message.is_mine ? <ReportButton messageId={message.id} eventId={eventId} disabled={message.reported} /> : null}
              </div>
              <p className="mt-1 whitespace-pre-line text-sm text-[var(--text-secondary)]">{message.body}</p>
              {message.reported ? <p className="mt-1 text-[11px] text-rose-600">Sudah dilaporkan.</p> : null}
            </div>
          ))}
        </div>

        {enabled ? (
          <form action={sendAction} className="flex flex-col gap-2 border-t border-[var(--border)] p-5">
            {sendState.error ? <p className="text-xs text-rose-600">{sendState.error}</p> : null}
            {sendState.ok ? <p className="text-xs text-emerald-700">Pesan terkirim.</p> : null}
            <textarea name="body" rows={2} required maxLength={2000} disabled={!canSend} placeholder={canSend ? "Tulis pesan…" : "Pengiriman pesan dinonaktifkan panitia."} className="rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 py-2 text-sm disabled:opacity-60" />
            <Button type="submit" variant="primary" disabled={!canSend || sending}>{sending ? "Mengirim…" : "Kirim Pesan"}</Button>
          </form>
        ) : null}
      </Card>
    </div>
  );
}

function ReportButton({ messageId, eventId, disabled }: { messageId: number; eventId: number; disabled: boolean }) {
  const [state, action, pending] = useActionState<ChatState, FormData>(reportMessageAction.bind(null, messageId, eventId), {});

  if (disabled) return null;

  return (
    <form action={action} className="flex items-center gap-1">
      <input name="reason" required maxLength={500} placeholder="Alasan laporan" className="w-32 rounded border border-[var(--border)] px-2 py-1 text-[11px]" />
      <Button type="submit" variant="secondary" size="sm" disabled={pending}>{pending ? "…" : "Laporkan"}</Button>
      {state.error ? <span className="text-[10px] text-rose-600">{state.error}</span> : null}
    </form>
  );
}
