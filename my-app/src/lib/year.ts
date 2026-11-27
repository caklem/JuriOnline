// Tahun footer dihitung sekali saat proses server dimuat; tidak dibaca ulang
// di dalam render sehingga aman untuk prerender.
export const CURRENT_YEAR = new Date().getFullYear();
