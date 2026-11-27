import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Badge, Card, CardHeader, EmptyState, PageHeader } from "@/components/kit/ui";
import { ActionButton } from "@/components/admin/action-button";
import { BackLink } from "@/components/admin/ui";
import { LoadError } from "@/components/admin/load-error";
import { formatDateTime } from "@/components/admin/format";
import type { AssignmentRow, FormTemplateRow, JuryOption } from "@/components/admin/types";
import { deleteAssignmentAction } from "./actions";
import { ResetJuryPasswordForm } from "@/components/admin/jury-account-forms";
import { JuryOnboarding } from "@/components/admin/jury-onboarding";
import { EventNav } from "@/components/admin/event-nav";

export const metadata = { title: "Kelola Assignment Form Juri — JURI.ON" };

export const instant = false;

type AssignmentsResponse = {
  event: { id: number; name: string };
  juries: JuryOption[];
  form_templates: FormTemplateRow[];
  form_template_presets?: { key: string; name: string }[];
  assignments: AssignmentRow[];
};

function assignmentTone(status: string): "success" | "danger" | "warning" {
  if (status === "accepted") return "success";
  if (status === "declined") return "danger";

  return "warning";
}

export default async function JuryAssignmentsPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  let data: AssignmentsResponse;

  try {
    data = await apiFetch<AssignmentsResponse>(`/admin/events/${id}/jury-assignments`);
  } catch (error) {
    return (
      <AppFrame>
        <LoadError
          title="Assignment juri tidak dapat dimuat"
          description={error instanceof Error ? error.message : "Terjadi kesalahan saat memuat assignment juri."}
        />
      </AppFrame>
    );
  }

  const templates = data.form_templates;

  return (
    <AppFrame>
      <div className="flex flex-col gap-6">
        <div>
          <BackLink href={`/admin/events/${data.event.id}`}>Kembali ke Detail Event</BackLink>
          <div className="mt-4">
            <PageHeader
              title="Kelola Assignment Form Juri"
              description={`Event: ${data.event.name}`}
              action={<Badge>{data.juries.length} juri tersedia</Badge>}
            />
          </div>
        </div>

        <EventNav eventId={data.event.id} />

        {templates.length > 0 ? (
          <Card>
            <CardHeader
              title="Tambah Juri"
              description="Buat akun juri baru, atau tambahkan akun juri yang sudah ada, lalu assign ke form penilaian."
            />
            <div className="p-5">
              <JuryOnboarding
                eventId={data.event.id}
                templates={templates.map((template) => ({ id: template.id, name: template.name }))}
                juries={data.juries}
              />
            </div>
          </Card>
        ) : null}

        {templates.length === 0 ? (
          <Card>
            <EmptyState
              title="Belum ada form penilaian!"
              description="Buat form penilaian terlebih dahulu sebelum meng-assign juri ke event ini."
            />
          </Card>
        ) : (
          templates.map((template) => {
            const assigned = data.assignments.filter(
              (assignment) => assignment.form_template_id === template.id,
            );
            return (
              <Card key={template.id}>
                <CardHeader
                  title={template.name}
                  description={`${template.criteria_count} kriteria penilaian`}
                  action={<Badge tone="info">{assigned.length} Juri Ter-assign</Badge>}
                />

                <div className="flex flex-col gap-5 p-5">
                  {assigned.length === 0 ? (
                    <p className="rounded-lg border border-dashed border-[var(--border)] bg-[var(--surface-muted)] px-4 py-4 text-sm text-[var(--text-secondary)]">
                      Belum ada juri di form ini. Gunakan panel Tambah Juri di atas untuk meng-assign.
                    </p>
                  ) : (
                    <div>
                      <h3 className="text-sm font-semibold text-[var(--foreground)]">
                        Juri yang Sudah Di-assign ({assigned.length})
                      </h3>
                      <div className="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        {assigned.map((assignment) => (
                          <div
                            key={assignment.id}
                            className="flex flex-col gap-3 rounded-xl border border-[var(--border)] bg-[var(--background)] p-4"
                          >
                            <div className="flex items-center gap-3">
                              <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-indigo-100 bg-indigo-50 text-xs font-bold uppercase tracking-wide text-indigo-700">
                                {(assignment.jury.name ?? "?").slice(0, 1)}
                              </span>
                              <div className="min-w-0">
                                <p className="truncate text-sm font-semibold text-[var(--foreground)]">
                                  {assignment.jury.name ?? "-"}
                                </p>
                                <p className="truncate text-xs text-[var(--text-secondary)]">
                                  {assignment.jury.email ?? "-"}
                                </p>
                              </div>
                            </div>

                            <div className="flex flex-wrap items-center gap-2">
                              {assignment.jury_role ? (
                                <span className="inline-flex items-center rounded-full border border-indigo-100 bg-indigo-50 px-2.5 py-0.5 text-[11px] font-semibold text-indigo-700">
                                  {assignment.jury_role}
                                </span>
                              ) : (
                                <span className="inline-flex items-center rounded-full border border-[var(--border)] bg-[var(--surface-muted)] px-2.5 py-0.5 text-[11px] font-medium italic text-[var(--text-muted)]">
                                  Belum ada posisi juri
                                </span>
                              )}
                              <Badge tone={assignmentTone(assignment.status)}>{assignment.status_label}</Badge>
                            </div>

                            <p className="text-[11px] text-[var(--text-muted)]">
                              Di-assign pada {formatDateTime(assignment.created_at)}
                            </p>

                            <div className="border-t border-[var(--border)] pt-3">
                              {assignment.can_reset_password ? (
                                <ResetJuryPasswordForm eventId={data.event.id} assignmentId={assignment.id} />
                              ) : (
                                <p className="text-[11px] text-[var(--text-secondary)]">
                                  Password hanya dapat diubah oleh panitia yang membuat akun ini.
                                </p>
                              )}
                            </div>

                            <div className="flex justify-end border-t border-[var(--border)] pt-3">
                              <ActionButton
                                action={deleteAssignmentAction.bind(null, data.event.id, assignment.id)}
                                variant="danger"
                                size="sm"
                                confirm={`Hapus assignment untuk ${assignment.jury.name ?? "juri ini"}?`}
                              >
                                Hapus
                              </ActionButton>
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              </Card>
            );
          })
        )}
      </div>
    </AppFrame>
  );
}
