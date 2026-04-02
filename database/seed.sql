-- Seed Data: Default Categories & Sample Account

-- Income categories (is_default = 1)
INSERT OR IGNORE INTO categories (id, name, type, icon, color, is_active, is_default) VALUES
(1,  'เงินเดือน',        'income',  '💰', '#22c55e', 1, 1),
(2,  'รายได้พิเศษ',      'income',  '🎁', '#10b981', 1, 1),
(3,  'ดอกเบี้ย',         'income',  '🏦', '#06b6d4', 1, 1),
(4,  'เงินปันผล',        'income',  '📈', '#8b5cf6', 1, 1),
(5,  'รายได้อื่นๆ',      'income',  '💵', '#84cc16', 1, 1);

-- Expense categories (is_default = 1)
INSERT OR IGNORE INTO categories (id, name, type, icon, color, is_active, is_default) VALUES
(6,  'อาหาร',           'expense', '🍜', '#f97316', 1, 1),
(7,  'เดินทาง',         'expense', '🚗', '#f59e0b', 1, 1),
(8,  'ที่พักอาศัย',     'expense', '🏠', '#ef4444', 1, 1),
(9,  'สุขภาพ',          'expense', '🏥', '#ec4899', 1, 1),
(10, 'ช้อปปิ้ง',        'expense', '🛍️', '#a855f7', 1, 1),
(11, 'บันเทิง',         'expense', '🎬', '#6366f1', 1, 1),
(12, 'ค่าสาธารณูปโภค', 'expense', '💡', '#14b8a6', 1, 1),
(13, 'การศึกษา',        'expense', '📚', '#0ea5e9', 1, 1),
(14, 'ประกัน',          'expense', '🛡️', '#78716c', 1, 1),
(15, 'อื่นๆ',           'expense', '📋', '#94a3b8', 1, 1),
(16, 'ครอบครัว',       'expense', '👨‍👩‍👧', '#f43f5e', 1, 1),
(17, 'ชำระหนี้',       'expense', '💳', '#dc2626', 1, 1),
(18, 'ไอที/โดเมน',     'expense', '🖥️', '#0284c7', 1, 1),
(19, 'ภาษี',           'expense', '🏛️', '#92400e', 1, 1);

-- Sample account
INSERT OR IGNORE INTO accounts (id, name, type, balance, color, icon, is_active) VALUES
(1, 'กระเป๋าสตางค์', 'cash', 5000.00, '#6366f1', '👛', 1),
(2, 'ธนาคาร กสิกร',  'bank', 25000.00, '#22c55e', '🏦', 1);
