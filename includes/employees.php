<?php
requireAdmin();

$users = Database::getAllUsers();
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col h-full">
    <div class="p-5 border-b border-slate-100 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Danh sách nhân viên</h3>
            <p class="text-sm text-slate-500 mt-1">Quản lý tài khoản nhân viên</p>
        </div>
        <button 
            onclick="document.getElementById('createEmployeeModal').classList.remove('hidden')"
            class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors flex items-center gap-2 shadow-sm"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Thêm nhân viên
        </button>
    </div>

    <?php if ($success === 'created'): ?>
        <div class="mx-5 mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
            Đã tạo tài khoản nhân viên thành công!
        </div>
    <?php endif; ?>

    <?php if ($success === 'deleted'): ?>
        <div class="mx-5 mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
            Đã xóa nhân viên thành công!
        </div>
    <?php endif; ?>

    <?php if ($error === 'exists'): ?>
        <div class="mx-5 mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
            Tên đăng nhập đã tồn tại!
        </div>
    <?php endif; ?>

    <div class="flex-1 overflow-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 sticky top-0 z-10">
                <tr>
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase">Tài khoản</th>
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase">Vai trò</th>
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase">Ngày tạo</th>
                    <th class="p-4 text-xs font-bold text-slate-500 uppercase text-right">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colSpan="4" class="p-8 text-center text-slate-400">Chưa có nhân viên nào</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full <?php echo $user['role'] === 'admin' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700'; ?> flex items-center justify-center font-bold text-sm">
                                        <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($user['username']); ?></p>
                                        <p class="text-xs text-slate-500">ID: <?php echo $user['id']; ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $user['role'] === 'admin' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'; ?>">
                                    <?php echo $user['role'] === 'admin' ? 'Quản trị viên' : 'Nhân viên'; ?>
                                </span>
                            </td>
                            <td class="p-4 text-sm text-slate-600">
                                <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>
                            </td>
                            <td class="p-4 text-right">
                                <?php if ($user['id'] != getCurrentUser()['id'] && $user['role'] !== 'admin'): ?>
                                    <form method="POST" action="" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhân viên này?');">
                                        <input type="hidden" name="delete_employee" value="1">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            Xóa
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-slate-400 text-sm">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Employee Modal -->
<div id="createEmployeeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-slate-800">Thêm nhân viên mới</h3>
            <button 
                onclick="document.getElementById('createEmployeeModal').classList.add('hidden')"
                class="text-slate-400 hover:text-slate-600"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="create_employee" value="1">
            
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tên đăng nhập</label>
                <input 
                    type="text" 
                    name="username"
                    required
                    class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-200 focus:border-emerald-500 outline-none text-slate-700"
                    placeholder="Nhập tên đăng nhập"
                />
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Mật khẩu</label>
                <input 
                    type="password" 
                    name="password"
                    required
                    minlength="6"
                    class="w-full px-4 py-3 bg-slate-50 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-200 focus:border-emerald-500 outline-none text-slate-700"
                    placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)"
                />
            </div>

            <div class="flex gap-3 pt-2">
                <button 
                    type="button"
                    onclick="document.getElementById('createEmployeeModal').classList.add('hidden')"
                    class="flex-1 px-4 py-3 bg-slate-100 text-slate-700 rounded-xl font-medium hover:bg-slate-200 transition-colors"
                >
                    Hủy
                </button>
                <button 
                    type="submit"
                    class="flex-1 px-4 py-3 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 transition-colors"
                >
                    Tạo tài khoản
                </button>
            </div>
        </form>
    </div>
</div>

