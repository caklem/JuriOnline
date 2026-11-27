export type NavIcon =
  | "dashboard"
  | "calendar"
  | "plus"
  | "wallet"
  | "receipt"
  | "award"
  | "users"
  | "user"
  | "bell"
  | "ticket";

export type NavItem = { href: string; label: string; icon: NavIcon; description?: string };
export type NavSection = { title: string; items: NavItem[] };

function section(title: string, items: NavItem[]): NavSection {
  return { title, items };
}

function item(href: string, label: string, icon: NavIcon, description?: string): NavItem {
  return { href, label, icon, description };
}

export function navSectionsFor(role: string, eventScope = "all"): NavSection[] {
  if (role === "admin_owner") {
    return [
      section("Ringkasan", [item("/dashboard", "Dashboard", "dashboard", "Ringkasan platform")]),
      section("Event", [
        item("/admin/events", "Event", "calendar", "Semua event"),
        item("/admin/events/create", "Buat Event", "plus", "Event baru"),
      ]),
      section("Keuangan", [
        item("/admin/payments", "Verifikasi Pembayaran", "wallet", "Antrean verifikasi"),
        item("/admin/payments/all", "Semua Pembayaran", "receipt", "Riwayat pembayaran"),
      ]),
      section("Penilaian", [
        item("/admin/scores", "Semua Penilaian", "award", "Nilai seluruh juri"),
        item("/owner/users", "Kelola Pengguna", "users", "Akun dan peran"),
      ]),
      section("Akun", [
        item("/profile", "Profil", "user"),
        item("/admin/organizer", "Profil Organizer", "user", "Identitas penyelenggara"),
      ]),
    ];
  }

  if (role === "admin") {
    const sections: NavSection[] = [
      section("Ringkasan", [item("/dashboard", "Dashboard", "dashboard", "Ringkasan kerja")]),
      section("Event", [
        item("/admin/events", "Event Saya", "calendar", "Event yang Anda kelola"),
        item("/admin/events/create", "Buat Event", "plus", "Event baru"),
      ]),
      section("Keuangan", [
        item("/admin/payments", "Verifikasi Pembayaran", "wallet", "Antrean verifikasi"),
      ]),
      section("Akun", [
        item("/profile", "Profil", "user"),
        item("/admin/organizer", "Profil Organizer", "user", "Identitas penyelenggara"),
      ]),
    ];

    if (eventScope !== "own") {
      sections[2].items.push(item("/admin/payments/all", "Semua Pembayaran", "receipt", "Riwayat pembayaran"));
      sections.splice(3, 0, section("Penilaian", [item("/admin/scores", "Semua Penilaian", "award", "Nilai seluruh juri")]));
    }

    return sections;
  }

  if (role === "jury") {
    return [
      section("Ringkasan", [item("/dashboard", "Dashboard", "dashboard")]),
      section("Penilaian", [
        item("/jury/invitations", "Undangan Juri", "ticket", "Undangan menunggu"),
        item("/jury/events", "Penilaian", "calendar", "Event yang Anda nilai"),
        item("/jury/revisions", "Permintaan Revisi", "bell", "Tinjau ulang nilai"),
      ]),
      section("Akun", [item("/profile", "Profil", "user")]),
    ];
  }

  return [
    section("Ringkasan", [item("/dashboard", "Dashboard", "dashboard")]),
    section("Kegiatan", [
      item("/events", "Event", "calendar", "Cari dan ikuti event"),
      item("/my-registrations", "Pendaftaran Saya", "ticket", "Status pendaftaran"),
    ]),
    section("Akun", [item("/profile", "Profil", "user")]),
  ];
}
