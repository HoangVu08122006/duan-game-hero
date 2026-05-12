My# Hero Slash - Game Design Document

## Giới thiệu

Hero Slash là game casual idle action, nơi người chơi điều khiển một anh hùng đứng tại chỗ và liên tục chiến đấu với quái vật phía trước.

Người chơi sẽ:
- Chém quái
- Nhận vàng và EXP
- Nâng cấp nhân vật
- Mở khóa vũ khí
- Triệu hồi pet hỗ trợ
- Đánh boss
- Leo stage ngày càng khó

Toàn bộ dữ liệu sẽ được lưu lên server thông qua API backend.

---

# Gameplay Loop

```text
Chém quái
→ Nhận vàng / EXP
→ Nâng cấp hero
→ Nâng cấp vũ khí
→ Mở pet mới
→ DPS mạnh hơn
→ Quái mạnh hơn
→ Tiếp tục progression
```

---

# Gameplay Chính

## Hero

- Hero đứng một vị trí cố định
- Tự động chém quái phía trước
- Có animation attack
- Có chỉ số:
  - Attack
  - Crit Rate
  - Crit Damage
  - Attack Speed
  - HP

---

## Monster

Quái:
- Spawn liên tục phía trước hero
- Có HP
- Có animation hit
- Có animation death

Khi bị đánh:
- Rung nhẹ
- Flash đỏ
- Damage text bay lên
- Knockback nhẹ

Khi chết:
- Rơi vàng
- Particle effect
- Spawn quái mới mạnh hơn

---

# Progression System

## Hero Upgrade

Người chơi dùng gold để:
- Tăng damage
- Tăng tốc đánh
- Tăng crit
- Tăng HP

---

## Weapon System

Ví dụ:
- Wooden Sword
- Silver Sword
- Fire Blade
- Demon Katana

Weapon có:
- Level
- Rarity
- Bonus stat

---

## Pet System

Pet hỗ trợ auto attack.

Ví dụ:
- Slime
- Wolf
- Mini Dragon
- Ghost

Pet có:
- DPS riêng
- Skill passive
- Level

---

# Stage System

- Quái ngày càng mạnh
- Boss xuất hiện mỗi 10 level
- Boss có:
  - Máu cao
  - Skill riêng
  - Reward lớn

---

# Reward System

## Gold
Dùng để:
- Upgrade hero
- Upgrade weapon

## Gem
Dùng để:
- Summon pet
- Mua chest

## EXP
Dùng để:
- Tăng level hero

---

# Backend Features

## Account System

- Guest Login
- Login bằng email
- JWT Authentication

---

## Save Data

Server lưu:
- Hero level
- Gold
- Gem
- Weapon
- Pet
- Stage

Ví dụ:

```json
{
  "gold": 1000,
  "hero_level": 5,
  "weapon_level": 3,
  "stage": 12
}
```

---

## Inventory System

Lưu:
- Weapon
- Pet
- Material
- Currency

---

## Leaderboard

Xếp hạng:
- Stage cao nhất
- Total Power
- Total Damage

---

## Daily Reward

- Login mỗi ngày nhận thưởng
- Reward tăng theo số ngày liên tiếp

---

## Shop System

Cho phép:
- Mua gem
- Mua chest
- Mua skin

---

# API Flow

## Login

```http
POST /api/login
```

---

## Save Player Data

```http
POST /api/player/save
```

---

## Get Inventory

```http
GET /api/inventory
```

---

## Upgrade Hero

```http
POST /api/hero/upgrade
```

---

## Summon Pet

```http
POST /api/pet/summon
```

---

# Future Features

## AFK Reward

Offline vẫn nhận:
- Gold
- EXP

Server tính theo thời gian offline.

---

## Gacha System

Quay:
- Weapon
- Pet
- Skin

---

## Skill Tree

Ví dụ:
- Crit branch
- Speed branch
- Pet branch

---

## Event System

Event giới hạn thời gian:
- Boss event
- Double gold
- Limited summon

---

# MVP Scope

## Core Features
- Chém quái
- Upgrade
- Pet
- Save server
- Inventory
- Leaderboard

## Optional Features
- Gacha
- Daily reward
- Boss
- AFK reward
