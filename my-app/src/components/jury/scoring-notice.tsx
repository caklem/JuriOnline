export function ScoringClosedNotice({
  mode,
  opensAt,
}: {
  mode: "open" | "closed" | "auto" | string;
  opensAt: string | null;
}) {
  return (
    <div className="rounded-xl border-l-4 border-amber-400 bg-amber-50 px-4 py-3">
      <p className="text-sm font-semibold text-amber-900">Penilaian belum dibuka</p>
      <p className="mt-1 text-xs leading-5 text-amber-800">
        {mode === "closed" ? (
          <>Panitia menutup penilaian untuk event ini sementara.</>
        ) : (
          <>
            Penilaian terbuka otomatis pada <strong>{opensAt ?? "hari H"}</strong>, atau lebih cepat bila
            panitia mengaktifkannya.
          </>
        )}{" "}
        Tombol <strong>Nilai Peserta</strong> akan muncul begitu penilaian dibuka.
      </p>
    </div>
  );
}
