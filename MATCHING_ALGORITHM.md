# 🎯 Thuật toán Đề xuất Người dùng Phù hợp (Matching Algorithm)

## 📊 Tổng quan

Hệ thống sử dụng thuật toán tính điểm tương thích (compatibility score) từ 0-100 dựa trên 4 yếu tố chính:

### 🏆 Trọng số các yếu tố:

| Yếu tố | Trọng số | Mô tả |
|--------|----------|-------|
| 💕 **Sở thích chung** | 35% | Tính % sở thích trùng khớp giữa 2 người |
| 💼 **Nghề nghiệp** | 25% | Cùng ngành nghề/lĩnh vực |
| 📍 **Vị trí** | 25% | Cùng thành phố |
| 🎂 **Độ tuổi** | 15% | Khoảng cách tuổi hợp lý |

---

## 🧮 Chi tiết tính điểm

### 1. Điểm Sở thích (35 điểm tối đa)

**Công thức:**
```
Điểm = (Số sở thích chung / Tổng số sở thích unique của cả 2) × 35
```

**Ví dụ:**
- User A có sở thích: [Âm nhạc, Du lịch, Đọc sách]
- User B có sở thích: [Du lịch, Đọc sách, Thể thao, Chụp ảnh]
- Sở thích chung: 2 (Du lịch, Đọc sách)
- Tổng sở thích unique: 5
- **Điểm = (2/5) × 35 = 14 điểm**

**Tại sao dùng công thức này?**
- ✅ Công bằng hơn so với đếm số sở thích chung
- ✅ Người có nhiều sở thích không bị lợi thế quá mức
- ✅ Khuyến khích sự tương đồng thực sự

---

### 2. Điểm Nghề nghiệp (25 điểm tối đa)

**Công thức:**
```
Điểm = 25 nếu cùng nghề nghiệp
       0  nếu khác nghề nghiệp
```

**Lý do:**
- Cùng nghề nghiệp → dễ hiểu nhau về công việc
- Có chủ đề chung để trò chuyện
- Thời gian làm việc tương đồng

---

### 3. Điểm Vị trí (25 điểm tối đa)

**Công thức:**
```
Điểm = 25 nếu cùng thành phố
       0  nếu khác thành phố
```

**Lý do:**
- Quan trọng cho hẹn hò thực tế
- Giảm khoảng cách địa lý
- Tăng khả năng gặp mặt

---

### 4. Điểm Độ tuổi (15 điểm tối đa)

**Công thức:**
```
Chênh lệch tuổi | Điểm
----------------|------
0 năm           | 15
1-2 năm         | 12
3-5 năm         | 8
6-10 năm        | 4
>10 năm         | 0
```

**Ví dụ:**
- User A: 25 tuổi
- User B: 27 tuổi → Chênh 2 năm → **12 điểm**
- User C: 30 tuổi → Chênh 5 năm → **8 điểm**
- User D: 40 tuổi → Chênh 15 năm → **0 điểm**

---

## 🎯 Ví dụ Tính điểm Thực tế

### Trường hợp 1: Độ tương thích cao (82%)

**User A (25 tuổi, TPHCM, Developer)**
- Sở thích: Âm nhạc, Du lịch, Chơi game, Đọc sách

**User B (26 tuổi, TPHCM, Developer)**
- Sở thích: Du lịch, Chơi game, Đọc sách, Xem phim

**Tính điểm:**
- ✅ Sở thích: 3 chung / 5 unique = (3/5) × 35 = **21 điểm**
- ✅ Nghề nghiệp: Cùng Developer = **25 điểm**
- ✅ Vị trí: Cùng TPHCM = **25 điểm**
- ✅ Tuổi: Chênh 1 năm = **12 điểm**
- **🏆 TỔNG: 83/100 điểm**

---

### Trường hợp 2: Độ tương thích trung bình (48%)

**User A (25 tuổi, TPHCM, Developer)**
- Sở thích: Âm nhạc, Du lịch, Chơi game, Đọc sách

**User C (30 tuổi, Hà Nội, Giáo viên)**
- Sở thích: Du lịch, Nấu ăn, Thể thao

**Tính điểm:**
- ⚠️ Sở thích: 1 chung / 6 unique = (1/6) × 35 = **5.8 điểm**
- ❌ Nghề nghiệp: Khác nhau = **0 điểm**
- ❌ Vị trí: Khác thành phố = **0 điểm**
- ✅ Tuổi: Chênh 5 năm = **8 điểm**
- **🎯 TỔNG: 13.8/100 điểm**

---

## 🔍 Bộ lọc nâng cao

Người dùng có thể lọc theo:

### 📍 Khu vực
```sql
WHERE hosonguoidung.maThanhPho = {selected_city}
```

### 💼 Nghề nghiệp
```sql
WHERE hosonguoidung.maNgheNghiep = {selected_job}
```

### 🎂 Độ tuổi
```sql
WHERE YEAR(CURDATE()) - YEAR(hosonguoidung.ngaysinh) BETWEEN {min_age} AND {max_age}
```

---

## 📈 Thứ tự ưu tiên

1. **Sắp xếp theo điểm tương thích** (giảm dần)
2. **Random trong cùng điểm** - tăng sự đa dạng
3. **Giới hạn 20 kết quả** - tránh quá tải

```sql
ORDER BY compatibility_score DESC, RAND()
LIMIT 20
```

---

## 🚫 Loại trừ

Hệ thống tự động loại trừ:
- ❌ Chính người dùng hiện tại
- ❌ Những người đã like
- ❌ Những người đã match
- ❌ Những người đã block

```sql
LEFT JOIN quanhenguoidung qh ON (...)
WHERE qh.maNguoiDung1 IS NULL
```

---

## 💡 Ưu điểm của thuật toán

1. ✅ **Công bằng**: Không thiên vị người có nhiều sở thích
2. ✅ **Linh hoạt**: Có thể điều chỉnh trọng số dễ dàng
3. ✅ **Hiệu quả**: Query SQL tối ưu, tính toán nhanh
4. ✅ **Minh bạch**: Người dùng thấy % tương thích
5. ✅ **Đa dạng**: Random trong cùng điểm số

---

## 🔧 Cải tiến trong tương lai

- [ ] Thêm trọng số học vấn
- [ ] Tính điểm dựa trên tương tác trước đó
- [ ] Machine Learning để học sở thích người dùng
- [ ] Thêm filter giới tính/hướng tính dục
- [ ] Điểm tính cách (personality quiz)

---

## 📊 Phân loại độ tương thích

| Điểm | Badge màu | Mô tả |
|------|-----------|-------|
| 70-100 | 💗 Hồng | Rất phù hợp |
| 40-69 | 🧡 Cam | Khá phù hợp |
| 0-39 | 💙 Xanh | Ít phù hợp |

---

## 🎓 Kiến thức áp dụng

- **Database**: MySQL subqueries, JOIN, aggregation
- **Algorithm**: Weighted scoring system
- **Math**: Percentage calculation, Jaccard similarity (modified)
- **UX**: Progressive disclosure, visual feedback
