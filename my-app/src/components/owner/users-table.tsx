"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { AnimatePresence, motion, useReducedMotion } from "motion/react";
import { Button } from "@/components/arc/button/button";
import { Badge, Table } from "@/components/kit/ui";
import { toggleStatusAction, updateRoleAction, updateScopeAction, type ActionState } from "@/app/owner/users/actions";
import { formatDate } from "./format";
import type { Role, UserRow } from "./types";

const SELECT_CLASS =
  "h-9 w-full min-w-[10rem] rounded-lg border border-[var(--border)] bg-[var(--background)] px-2.5 text-sm text-[var(--foreground)] outline-none transition focus:border-[var(--accent)] focus:ring-2 focus:ring-[var(--accent-subtle)] disabled:cursor-not-allowed disabled:opacity-60";

type Feedback = { tone: "success" | "error"; text: string };

export function UsersTable({
  users,
  roles,
  selfId,
}: {
  users: UserRow[];
  roles: Role[];
  selfId: number;
}) {
  const [busy, setBusy] = useState<string | null>(null);
  const [feedback, setFeedback] = useState<Feedback | null>(null);
  const reduced = useReducedMotion();
  const router = useRouter();

  async function submit(
    key: string,
    action: (prev: ActionState, formData: FormData) => Promise<ActionState>,
    form: HTMLFormElement,
  ) {
    setBusy(key);
    setFeedback(null);

    const result = await action({}, new FormData(form));

    setBusy(null);

    if (result.error) {
      setFeedback({ tone: "error", text: result.error });
    } else if (result.success) {
      setFeedback({ tone: "success", text: result.success });
      router.refresh();
    }
  }

  return (
    <div>
      <AnimatePresence initial={false}>
        {feedback ? (
          <motion.div
            key="feedback"
            role={feedback.tone === "error" ? "alert" : "status"}
            initial={reduced ? false : { opacity: 0, y: -4 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0 }}
            className={`mx-5 mt-4 rounded-lg px-3 py-2 text-xs font-medium ${
              feedback.tone === "error"
                ? "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-100"
                : "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-100"
            }`}
          >
            {feedback.text}
          </motion.div>
        ) : null}
      </AnimatePresence>

      <Table head={["Pengguna", "Role", "Akses Event", "Status", "Terdaftar", "Aksi"]}>
        {users.map((user, index) => {
          const isSelf = user.id === selfId;

          return (
            <motion.tr
              key={user.id}
              initial={reduced ? false : { opacity: 0, y: 6 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: reduced ? 0 : 0.24, delay: reduced ? 0 : Math.min(index, 10) * 0.025 }}
              className="align-top"
            >
              <td className="px-5 py-4">
                <div className="flex flex-wrap items-center gap-2">
                  <strong className="font-semibold text-[var(--foreground)]">{user.name}</strong>
                  {isSelf ? <Badge tone="info">Akun Anda</Badge> : null}
                  {user.is_event_admin ? <Badge tone="warning">Admin Event</Badge> : null}
                </div>
                <p className="mt-0.5 text-xs text-[var(--text-secondary)]">{user.email}</p>
              </td>

              <td className="px-5 py-4">
                <form
                  onSubmit={(event) => {
                    event.preventDefault();
                    void submit(`role-${user.id}`, updateRoleAction, event.currentTarget);
                  }}
                  className="flex items-center gap-2"
                >
                  <input type="hidden" name="user_id" value={user.id} />
                  {user.is_admin ? <input type="hidden" name="event_scope" value={user.event_scope ?? "all"} /> : null}
                  <label className="sr-only" htmlFor={`role-${user.id}`}>
                    Role {user.name}
                  </label>
                  <select
                    key={`${user.id}-${user.role_id}`}
                    id={`role-${user.id}`}
                    name="role_id"
                    className={SELECT_CLASS}
                    defaultValue={user.role_id ?? ""}
                    disabled={isSelf}
                  >
                    {roles.map((role) => (
                      <option key={role.id} value={role.id}>
                        {role.display_name}
                      </option>
                    ))}
                  </select>
                  {!isSelf ? (
                    <Button type="submit" variant="secondary" size="sm" loading={busy === `role-${user.id}`}>
                      Simpan
                    </Button>
                  ) : null}
                </form>
              </td>

              <td className="px-5 py-4">
                {user.is_admin ? (
                  <form
                    onSubmit={(event) => {
                      event.preventDefault();
                      void submit(`scope-${user.id}`, updateScopeAction, event.currentTarget);
                    }}
                    className="flex items-center gap-2"
                  >
                    <input type="hidden" name="user_id" value={user.id} />
                    <label className="sr-only" htmlFor={`scope-${user.id}`}>
                      Akses event {user.name}
                    </label>
                    <select
                      key={`${user.id}-${user.event_scope}`}
                      id={`scope-${user.id}`}
                      name="event_scope"
                      className={SELECT_CLASS}
                      defaultValue={user.event_scope ?? "all"}
                    >
                      <option value="all">Semua event</option>
                      <option value="own">Hanya event sendiri</option>
                    </select>
                    <Button type="submit" variant="secondary" size="sm" loading={busy === `scope-${user.id}`}>
                      Simpan
                    </Button>
                  </form>
                ) : (
                  <span className="text-slate-500">-</span>
                )}
              </td>

              <td className="px-5 py-4">
                {user.is_active ? <Badge tone="success">Aktif</Badge> : <Badge tone="danger">Nonaktif</Badge>}
              </td>

              <td className="px-5 py-4 text-[var(--text-secondary)]">{formatDate(user.created_at)}</td>

              <td className="px-5 py-4 text-right">
                {isSelf ? (
                  <span className="text-xs text-slate-400">Tidak tersedia</span>
                ) : (
                  <form
                    onSubmit={(event) => {
                      event.preventDefault();
                      void submit(`status-${user.id}`, toggleStatusAction, event.currentTarget);
                    }}
                  >
                    <input type="hidden" name="user_id" value={user.id} />
                    <Button
                      type="submit"
                      variant={user.is_active ? "secondary" : "primary"}
                      size="sm"
                      loading={busy === `status-${user.id}`}
                    >
                      {user.is_active ? "Nonaktifkan" : "Aktifkan"}
                    </Button>
                  </form>
                )}
              </td>
            </motion.tr>
          );
        })}
      </Table>

      <p className="border-t border-[var(--border)] px-5 py-3 text-[11px] text-[var(--text-muted)]">
        Perubahan role, akses event, dan status langsung tersimpan ke akun pengguna.
      </p>
    </div>
  );
}
