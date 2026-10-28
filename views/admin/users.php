<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="/admin" class="hover:text-cyan-400 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali ke Admin
        </a>
        <span>/</span>
        <span class="text-white">Pengurusan Pengguna</span>
    </div>

    <div>
        <h1 class="text-2xl font-extrabold text-white">Senarai Pengguna & Peranan</h1>
        <p class="text-xs text-slate-400">Tukar peranan pengguna (Admin, Creator, Viewer) atau aktifkan pas VIP secara manual.</p>
    </div>

    <div class="rounded-3xl bg-slate-900 border border-white/5 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-white/5 text-slate-400 bg-white/[0.02]">
                        <th class="py-3 px-4">Nama & Emel</th>
                        <th class="py-3 px-4">Peranan Semasa</th>
                        <th class="py-3 px-4">Status VIP</th>
                        <th class="py-3 px-4">Tarikh Luput VIP</th>
                        <th class="py-3 px-4 text-right">Tindakan Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-white/[0.02]">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white"><?= htmlspecialchars($u['name']) ?></div>
                                <div class="text-[11px] text-slate-500"><?= htmlspecialchars($u['email']) ?></div>
                            </td>
                            <td class="py-3.5 px-4">
                                <form action="/admin/user/<?= $u['id'] ?>/role" method="POST" class="inline-flex">
                                    <select name="role" onchange="this.form.submit()" 
                                            class="bg-slate-950 text-xs text-slate-200 px-2.5 py-1 rounded-lg border border-white/10 focus:outline-none focus:border-cyan-500">
                                        <option value="viewer" <?= $u['role'] === 'viewer' ? 'selected' : '' ?>>Viewer (Penonton)</option>
                                        <option value="creator" <?= $u['role'] === 'creator' ? 'selected' : '' ?>>Creator (Filmmaker)</option>
                                        <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin (Super Admin)</option>
                                    </select>
                                </form>
                            </td>
                            <td class="py-3.5 px-4">
                                <?php if ($u['subscription_status'] === 'premium'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                        <i data-lucide="crown" class="w-3 h-3 fill-amber-400"></i> VIP Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-slate-500">Free (Dengan Iklan)</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-400">
                                <?= !empty($u['subscription_expires_at']) ? date('d M Y, h:i A', strtotime($u['subscription_expires_at'])) : '-' ?>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="/admin/user/<?= $u['id'] ?>/vip" method="POST">
                                        <button type="submit" 
                                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors <?= $u['subscription_status'] === 'premium' ? 'bg-red-500/10 text-red-400 hover:bg-red-500/20' : 'bg-amber-500/10 text-amber-300 hover:bg-amber-500/20 border border-amber-500/30' ?>">
                                            <?= $u['subscription_status'] === 'premium' ? 'Tarik Balik VIP' : '+ Beri Pas VIP (30 Hari)' ?>
                                        </button>
                                    </form>

                                    <a href="/demo/switch/<?= $u['id'] ?>" class="px-2 py-1 rounded-lg bg-cyan-500/10 text-cyan-300 text-xs hover:bg-cyan-500/20" title="Beralih ke akaun ini">
                                        Masuk
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
