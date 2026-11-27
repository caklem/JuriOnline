"use client";

import { useState, useTransition } from "react";
import { Button } from "@/components/arc/button/button";
import { Badge, Card, CardHeader } from "@/components/kit/ui";
import { sendChatMessageAction, setMessageHiddenAction, updateChatSettingsAction } from "./actions";
import type { ActionResult } from "../../../_lib/api-action";

type Settings = {
  enabled: boolean;
  participants_can_send: boolean;
  juries_can_send: boolean;
  whatsapp_enabled: boolean;
  whatsapp_group_url: string | null;
  announcement: string | null;
};

type Message = {
  id: number;
  body: string;
  sender: { id: number | null; name: string; role: string | null };
  created_at: string | null;
  reported: boolean;
  report_reason: string | null;
  is_hidden: boolean;
  is_mine: boolean;
};

export function ChatAdmin({ eventId, settings, messages }: { eventId: number; settings: Settings; messages: Message[] }) {
  const [state, setState] = useState<ActionResult>({});
  const [pending, startTransition] = useTransition();
  const [body, setBody] = useState("");

  function submitSettings(formData: FormData) {
    startTransition(async () => setState(await updateChatSettingsAction(eventId, formData)));
  }

  function send() {
    if (!body.trim()) return;
    startTransition(async () => {
      const result = await sendChatMessageAction(eventId, body.trim());
      setState(result);
      if (result.ok) setBody("");
    });
  }

  function toggleHidden(message: Message) {
    startTransition(async () => setState(await setMessageHiddenAction(message.id, eventId, !message.is_hidden)));
  }

  return (
    <div className="grid gap-5 lg:grid-cols-[340px_1fr]">
      <Card>
        <CardHeader title="Pengaturan Chat" description="Chat hanya aktif jika diizinkan." />
        <form action={submitSettings} className="flex flex-col gap-3 p-5 text-sm">
          {state.error ? <p className="text-xs text-rose-600">{state.error}</p> : null}
          {state.ok ? <p className="text-xs text-emerald-700">{state.message}</p> : null}
          <label className="flex items-center gap-2"><input type="checkbox" name="enabled" value="1" defaultChecked={settings.enabled} /> Aktifkan chat event</label>
          <label className="flex items-center gap-2"><input type="checkbox" name="participants_can_send" value="1" defaultChecked={settings.participants_can_send} /> Peserta dapat mengirim</label>
          <label className="flex items-center gap-2"><input type="checkbox" name="juries_can_send" value="1" defaultChecked={settings.juries_can_send} /> Juri dapat mengirim</label>
          <label className="flex items-center gap-2"><input type="checkbox" name="whatsapp_enabled" value="1" defaultChecked={settings.whatsapp_enabled} /> Tampilkan tautan WhatsApp</label>
          <label className="flex flex-col gap-1 font-medium">
            URL Grup WhatsApp
            <input name="whatsapp_group_url" defaultValue={settings.whatsapp_group_url ?? ""} placeholder="https://chat.whatsapp.com/..." className="rounded-lg border border-[var(--border)] bg-[var(--background)] px-2.5 py-2 text-xs" />
          </label>
          <label className="flex flex-col gap-1 font-medium">
            Pengumuman
            <textarea name="announcement" rows={3} defaultValue={settings.announcement ?? ""} maxLength={500} className="rounded-lg border border-[var(--border)] bg-[var(--background)] px-2.5 py-2 text-xs" />
          </label>
          <Button type="submit" variant="primary" disabled={pending}>{pending ? "Menyimpan…" : "Simpan Pengaturan"}</Button>
        </form>
      </Card>

      <Card>
        <CardHeader title={`${messages.length} pesan`} description="Pesan yang dilaporkan diberi tanda. Sembunyikan pesan yang melanggar." />
        <div className="max-h-[520px] space-y-3 overflow-y-auto p-5">
          {messages.length === 0 ? <p className="text-sm text-[var(--text-secondary)]">Belum ada pesan.</p> : messages.map((message) => (
            <div key={message.id} className={`rounded-xl border p-3 ${message.is_hidden ? "border-rose-200 bg-rose-50 opacity-70" : "border-[var(--border)] bg-[var(--background)]"}`}>
              <div className="flex flex-wrap items-center justify-between gap-2">
                <p className="text-xs font-semibold text-[var(--foreground)]">
                  {message.sender.name} <span className="font-normal text-[var(--text-muted)]">({message.sender.role ?? "-"})</span>
                </p>
                <div className="flex items-center gap-2">
                  {message.reported ? <Badge tone="danger">Dilaporkan</Badge> : null}
                  <Button type="button" variant="secondary" size="sm" onClick={() => toggleHidden(message)}>
                    {message.is_hidden ? "Tampilkan" : "Sembunyikan"}
                  </Button>
                </div>
              </div>
              <p className="mt-1 whitespace-pre-line text-sm text-[var(--text-secondary)]">{message.body}</p>
              {message.report_reason ? <p className="mt-1 text-xs text-rose-600">Alasan laporan: {message.report_reason}</p> : null}
            </div>
          ))}
        </div>
        <div className="flex flex-col gap-2 border-t border-[var(--border)] p-5">
          <textarea value={body} onChange={(event) => setBody(event.target.value)} rows={2} maxLength={2000} placeholder="Tulis pesan sebagai panitia…" className="rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 py-2 text-sm" />
          <Button type="button" variant="primary" disabled={pending || !body.trim()} onClick={send}>Kirim Pesan</Button>
        </div>
      </Card>
    </div>
  );
}
