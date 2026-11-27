import { PublicShell } from "@/components/public/public-shell";
import { CompanyProfileContent } from "@/components/public/company-profile-content";

// Halaman publik dirender per request (footer memakai tahun berjalan).
export const instant = false;

export const metadata = {
  title: "Company Profile - JURI.ON",
  description: "Tentang JURI.ON — platform penilaian digital yang membantu peserta memahami evaluasi juri agar dapat berkembang.",
};

export default function CompanyProfilePage() {
  return (
    <PublicShell>
      <CompanyProfileContent />
    </PublicShell>
  );
}
