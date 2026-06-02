# 🎨 Готовые пресеты стиля «Конференции.РФ»

Этот файл — каталог из 5 готовых тем для `assets/style.css`. Все они работают через **одну точку входа** — блок `:root { ... }` в самом начале CSS-файла.

## Как применить пресет

1. Открой `assets/style.css`.
2. Найди в начале файла блок `:root { ... }` (примерно строки 18–79).
3. **Полностью замени** его на блок `:root { ... }` из выбранного пресета ниже.
4. Сохрани и обнови страницу в браузере. **Если стиль не меняется** — сделай жёсткую перезагрузку: `Ctrl+F5` (Windows) или `Cmd+Shift+R` (Mac).
5. Можно копировать пресет не целиком, а **отдельными секциями** — например, взять цвета из «Sunset Coral», но радиусы оставить от «Emerald».

## Переменные, которые ты можешь менять

| Группа | Переменные | Зачем |
|---|---|---|
| **Палитра** | `--gray-*`, `--text-muted`, `--white` | базовые серые тона и фоны |
| **Бренд** | `--green`, `--green-rgb`, `--green-dark`, `--green-darker` | основной акцент сайта. Поменяй `--green` — и кнопки, ссылки, активные состояния поменяют цвет. **Обязательно меняй `--green-rgb` вместе с `--green`** — это используется в тенях и focus-кольце через `rgba(var(--green-rgb), …)`. |
| **Статусы** | `--success-*`, `--danger*`, `--warning*`, `--info*` | цвета бейджей статусов заявок |
| **Радиусы** | `--radius-sm/md/lg/xl/2xl/pill/pill-lg/full/circle` | закругления. Меняешь — меняются все блоки/кнопки сразу |
| **Переходы** | `--transition-fast`, `--transition` | скорость и кривая анимаций hover/focus |
| **Тени** | `--shadow-xs/sm/md/lg`, `--shadow-card-hover`, `--shadow-green-sm/md/soft`, `--ring-focus` | глубина и стиль теней |

## Известное ограничение

В `style.css` есть SVG-волна (`.wave-bg`, строка ~556) с зашитым цветом `rgba(40,167,69,0.05)` внутри `data:`-URL. Этот цвет **нельзя** подставить через `var(...)` — это не CSS, а строка-URL. Если меняешь бренд на не-зелёный, найди в файле фразу `rgba(40,167,69,0.05)` и замени RGB вручную. Это единственное место.

---

## 1. 🌿 Emerald (изумруд) — дефолт

Изначальная тема: чистая, светлая, доверительная. Основной цвет — изумрудный, скруглённые «таблеточные» кнопки, плавные переходы. Подходит для корпоративных сайтов и системы бронирования.

```css
:root {
    /* ----- Базовая палитра ----- */
    --gray-dark: #343A40;
    --gray-dark-rgb: 52, 58, 64;
    --gray-medium: #495057;
    --gray-light: #CED4DA;
    --gray-bg: #F4F6F8;
    --gray-bg-soft: #eef2f5;
    --text-muted: #6c757d;
    --white: #FFFFFF;

    /* ----- Бренд (зелёный) ----- */
    --green: #28A745;
    --green-rgb: 40, 167, 69;
    --green-dark: #218838;
    --green-darker: #1e7e34;

    /* ----- Семантика статусов ----- */
    --success-bg: #e3f5e8;
    --success-bg-soft: #f4fbf6;
    --success-bg-alt: #d4edda;
    --success-text: #155724;
    --danger: #dc3545;
    --danger-bg: #f8d7da;
    --danger-text: #721c24;
    --warning-bg: #fff3cd;
    --warning-text: #856404;
    --info-bg: #d1ecf1;
    --info-text: #0c5460;

    /* ----- Радиусы ----- */
    --radius-sm:      12px;
    --radius-md:      16px;
    --radius-lg:      20px;
    --radius-xl:      24px;
    --radius-2xl:     28px;
    --radius-pill:    40px;
    --radius-pill-lg: 60px;
    --radius-full:    999px;
    --radius-circle:  50%;
    --border-radius:  var(--radius-md);

    /* ----- Переходы ----- */
    --transition-fast: 0.2s ease;
    --transition:      0.25s ease;

    /* ----- Тени ----- */
    --shadow-xs:          0 2px 8px  rgba(0, 0, 0, 0.04);
    --shadow-sm:          0 8px 20px rgba(0, 0, 0, 0.05);
    --shadow-md:          0 12px 28px rgba(0, 0, 0, 0.08);
    --shadow-lg:          0 20px 40px rgba(0, 0, 0, 0.08);
    --shadow:             0 10px 30px rgba(0, 0, 0, 0.08);
    --shadow-card-hover:  0 12px 24px rgba(0, 0, 0, 0.08);
    --shadow-green-sm:    0 4px 12px rgba(var(--green-rgb), 0.30);
    --shadow-green-md:    0 6px 14px rgba(var(--green-rgb), 0.30);
    --shadow-green-soft:  0 12px 24px rgba(var(--green-rgb), 0.18);
    --ring-focus:         0 0 0 3px  rgba(var(--green-rgb), 0.12);
}
```

---

## 2. 🌑 Midnight (тёмная) — dark mode

Тёмный фон, светлый текст, фиолетово-сиреневый акцент. Острые/маленькие радиусы — технологичный «продуктовый» вид. Подходит, если хочешь dark-theme.

> **Важно**: ⚠️ Эта тема инвертирует логику переменных. `--white` теперь не «белый», а «цвет карточек» (тёмно-серый). Не пугайся — это сделано, чтобы остальной CSS не пришлось переписывать. Также SVG-волна `.wave-bg` останется зелёной — см. «Известное ограничение» в начале файла, при желании поменяй её фоновый цвет вручную.

```css
:root {
    /* ----- Базовая палитра (инверсия) ----- */
    --gray-dark:      #F8F9FA;            /* «тёмный» теперь светлый — это цвет текста */
    --gray-dark-rgb:  248, 249, 250;
    --gray-medium:    #ADB5BD;
    --gray-light:     #495057;            /* «светлый» теперь тёмный — для рамок */
    --gray-bg:        #1A1D23;            /* основной фон страницы */
    --gray-bg-soft:   #22262E;
    --text-muted:     #9CA3AF;
    --white:          #2A2F38;            /* «белые» карточки теперь тоже тёмные */

    /* ----- Бренд (фиолетовый неон) ----- */
    --green:          #A78BFA;
    --green-rgb:      167, 139, 250;
    --green-dark:     #8B5CF6;
    --green-darker:   #7C3AED;

    /* ----- Семантика статусов (приглушённые на тёмном фоне) ----- */
    --success-bg:       #1F3A2E;
    --success-bg-soft:  #15281F;
    --success-bg-alt:   #1F3A2E;
    --success-text:     #5DD39E;
    --danger:           #F43F5E;
    --danger-bg:        #3A1F24;
    --danger-text:      #FDA4AF;
    --warning-bg:       #3A2F1F;
    --warning-text:     #FCD34D;
    --info-bg:          #1F2F3A;
    --info-text:        #93C5FD;

    /* ----- Радиусы (острые, технологичные) ----- */
    --radius-sm:      4px;
    --radius-md:      8px;
    --radius-lg:      10px;
    --radius-xl:      12px;
    --radius-2xl:     14px;
    --radius-pill:    8px;
    --radius-pill-lg: 12px;
    --radius-full:    999px;
    --radius-circle:  50%;
    --border-radius:  var(--radius-md);

    /* ----- Переходы (быстрее) ----- */
    --transition-fast: 0.15s ease;
    --transition:      0.20s ease;

    /* ----- Тени (глубже, с оттенком бренда) ----- */
    --shadow-xs:          0 2px 8px   rgba(0, 0, 0, 0.30);
    --shadow-sm:          0 4px 12px  rgba(0, 0, 0, 0.40);
    --shadow-md:          0 8px 20px  rgba(0, 0, 0, 0.50);
    --shadow-lg:          0 16px 40px rgba(0, 0, 0, 0.50);
    --shadow:             0 10px 30px rgba(0, 0, 0, 0.40);
    --shadow-card-hover:  0 12px 24px rgba(0, 0, 0, 0.50);
    --shadow-green-sm:    0 4px 16px  rgba(var(--green-rgb), 0.40);
    --shadow-green-md:    0 6px 20px  rgba(var(--green-rgb), 0.50);
    --shadow-green-soft:  0 0 24px    rgba(var(--green-rgb), 0.40);
    --ring-focus:         0 0 0 3px   rgba(var(--green-rgb), 0.30);
}
```

---

## 3. 🌅 Sunset Coral (закатный коралл) — тёплая

Кремовый фон, коралл-оранжевый акцент, очень мягкие округлые формы. Уютно и дружелюбно — для лайфстайл-сайтов, ивент-площадок, кофеен.

```css
:root {
    /* ----- Базовая палитра (тёплые тона) ----- */
    --gray-dark:      #4A3B36;            /* тёплый коричневатый текст */
    --gray-dark-rgb:  74, 59, 54;
    --gray-medium:    #6B5853;
    --gray-light:     #E8D5CC;
    --gray-bg:        #FFF8F3;            /* кремовый */
    --gray-bg-soft:   #FFEFE4;
    --text-muted:     #8B7B74;
    --white:          #FFFFFF;

    /* ----- Бренд (коралл) ----- */
    --green:          #FF6B6B;
    --green-rgb:      255, 107, 107;
    --green-dark:     #FF5252;
    --green-darker:   #E53E3E;

    /* ----- Семантика статусов ----- */
    --success-bg:       #DCF5E0;
    --success-bg-soft:  #F0FAF2;
    --success-bg-alt:   #C8EBCC;
    --success-text:     #2E7D32;
    --danger:           #C62828;
    --danger-bg:        #FFE0E0;
    --danger-text:      #8B0000;
    --warning-bg:       #FFF4D6;
    --warning-text:     #B8860B;
    --info-bg:          #E3F2FD;
    --info-text:        #1565C0;

    /* ----- Радиусы (мягкие, очень округлые) ----- */
    --radius-sm:      14px;
    --radius-md:      20px;
    --radius-lg:      28px;
    --radius-xl:      36px;
    --radius-2xl:     44px;
    --radius-pill:    999px;             /* все «pill» полностью круглые */
    --radius-pill-lg: 999px;
    --radius-full:    999px;
    --radius-circle:  50%;
    --border-radius:  var(--radius-md);

    /* ----- Переходы (плавные, тягучие) ----- */
    --transition-fast: 0.25s ease-out;
    --transition:      0.35s cubic-bezier(0.4, 0, 0.2, 1);

    /* ----- Тени (мягкие, тёплые) ----- */
    --shadow-xs:          0 2px 12px  rgba(var(--green-rgb), 0.08);
    --shadow-sm:          0 8px 24px  rgba(var(--green-rgb), 0.10);
    --shadow-md:          0 12px 32px rgba(var(--green-rgb), 0.12);
    --shadow-lg:          0 24px 48px rgba(var(--green-rgb), 0.14);
    --shadow:             0 12px 36px rgba(var(--green-rgb), 0.12);
    --shadow-card-hover:  0 16px 32px rgba(var(--green-rgb), 0.15);
    --shadow-green-sm:    0 4px 16px  rgba(var(--green-rgb), 0.35);
    --shadow-green-md:    0 8px 20px  rgba(var(--green-rgb), 0.40);
    --shadow-green-soft:  0 16px 32px rgba(var(--green-rgb), 0.25);
    --ring-focus:         0 0 0 4px   rgba(var(--green-rgb), 0.20);
}
```

---

## 4. 🌊 Ocean Depth (морская глубина) — прохладная

Светло-голубоватый фон, бирюзовый/teal акцент, сбалансированные радиусы. Деловая и спокойная — для b2b и аналитических панелей.

```css
:root {
    /* ----- Базовая палитра (холодные тона) ----- */
    --gray-dark:      #1A2C42;
    --gray-dark-rgb:  26, 44, 66;
    --gray-medium:    #2D4A6E;
    --gray-light:     #B8D4E3;
    --gray-bg:        #F0F7FB;            /* светлый голубоватый */
    --gray-bg-soft:   #E0EFF8;
    --text-muted:     #5B7A95;
    --white:          #FFFFFF;

    /* ----- Бренд (бирюзовый) ----- */
    --green:          #0891B2;
    --green-rgb:      8, 145, 178;
    --green-dark:     #0E7490;
    --green-darker:   #155E75;

    /* ----- Семантика статусов ----- */
    --success-bg:       #D1FAE5;
    --success-bg-soft:  #ECFDF5;
    --success-bg-alt:   #A7F3D0;
    --success-text:     #064E3B;
    --danger:           #DC2626;
    --danger-bg:        #FEE2E2;
    --danger-text:      #7F1D1D;
    --warning-bg:       #FEF3C7;
    --warning-text:     #78350F;
    --info-bg:          #DBEAFE;
    --info-text:        #1E3A8A;

    /* ----- Радиусы (умеренные, сбалансированные) ----- */
    --radius-sm:      6px;
    --radius-md:      10px;
    --radius-lg:      14px;
    --radius-xl:      18px;
    --radius-2xl:     22px;
    --radius-pill:    24px;
    --radius-pill-lg: 32px;
    --radius-full:    999px;
    --radius-circle:  50%;
    --border-radius:  var(--radius-md);

    /* ----- Переходы (деловые) ----- */
    --transition-fast: 0.18s ease;
    --transition:      0.22s ease;

    /* ----- Тени (холодные, чёткие) ----- */
    --shadow-xs:          0 1px 4px   rgba(var(--green-rgb), 0.06);
    --shadow-sm:          0 4px 12px  rgba(var(--green-rgb), 0.08);
    --shadow-md:          0 8px 20px  rgba(var(--green-rgb), 0.10);
    --shadow-lg:          0 16px 32px rgba(var(--green-rgb), 0.12);
    --shadow:             0 8px 24px  rgba(var(--green-rgb), 0.10);
    --shadow-card-hover:  0 10px 20px rgba(var(--green-rgb), 0.14);
    --shadow-green-sm:    0 4px 10px  rgba(var(--green-rgb), 0.30);
    --shadow-green-md:    0 6px 14px  rgba(var(--green-rgb), 0.35);
    --shadow-green-soft:  0 8px 20px  rgba(var(--green-rgb), 0.20);
    --ring-focus:         0 0 0 3px   rgba(var(--green-rgb), 0.20);
}
```

---

## 5. ⚡ Cyber Neon (киберпанк-неон) — контрастная

Почти чёрный фон, ядовито-зелёный акцент, острые углы, мгновенные анимации, неоновое свечение в тенях. Для технологичных/игровых сайтов.

> **Важно**: ⚠️ Как и Midnight, эта тема инвертирует значения переменных `--gray-*` и `--white`. Так задумано: остальной CSS-код не приходится менять. SVG-волна `.wave-bg` останется зелёной — это совпадает с цветом темы.

```css
:root {
    /* ----- Базовая палитра (тёмная) ----- */
    --gray-dark:      #E8F1F2;            /* «тёмный» теперь светлый = цвет текста */
    --gray-dark-rgb:  232, 241, 242;
    --gray-medium:    #AAB5B7;
    --gray-light:     #3D4751;
    --gray-bg:        #0A0E14;            /* почти чёрный фон */
    --gray-bg-soft:   #131820;
    --text-muted:     #7A8A95;
    --white:          #1A1F2A;            /* «белые» карточки тёмные */

    /* ----- Бренд (неон-зелёный) ----- */
    --green:          #00FF88;
    --green-rgb:      0, 255, 136;
    --green-dark:     #00E676;
    --green-darker:   #00C853;

    /* ----- Семантика статусов (высокий контраст) ----- */
    --success-bg:       #003D1F;
    --success-bg-soft:  #002814;
    --success-bg-alt:   #003D1F;
    --success-text:     #00FF88;
    --danger:           #FF0044;
    --danger-bg:        #3D0011;
    --danger-text:      #FF6688;
    --warning-bg:       #3D2900;
    --warning-text:     #FFC107;
    --info-bg:          #001F3D;
    --info-text:        #00B0FF;

    /* ----- Радиусы (острые, агрессивные) ----- */
    --radius-sm:      0px;
    --radius-md:      2px;
    --radius-lg:      4px;
    --radius-xl:      6px;
    --radius-2xl:     8px;
    --radius-pill:    2px;
    --radius-pill-lg: 4px;
    --radius-full:    999px;
    --radius-circle:  50%;
    --border-radius:  var(--radius-md);

    /* ----- Переходы (мгновенные) ----- */
    --transition-fast: 0.10s ease;
    --transition:      0.12s ease;

    /* ----- Тени (неоновое свечение) ----- */
    --shadow-xs:          0 0 8px   rgba(var(--green-rgb), 0.10);
    --shadow-sm:          0 0 16px  rgba(var(--green-rgb), 0.15);
    --shadow-md:          0 0 24px  rgba(var(--green-rgb), 0.20);
    --shadow-lg:          0 0 40px  rgba(var(--green-rgb), 0.25);
    --shadow:             0 0 30px  rgba(var(--green-rgb), 0.20);
    --shadow-card-hover:  0 0 24px  rgba(var(--green-rgb), 0.30);
    --shadow-green-sm:    0 0 12px  rgba(var(--green-rgb), 0.60);
    --shadow-green-md:    0 0 20px  rgba(var(--green-rgb), 0.80);
    --shadow-green-soft:  0 0 32px  rgba(var(--green-rgb), 0.40);
    --ring-focus:         0 0 0 2px rgba(var(--green-rgb), 0.60);
}
```

---

## Шпаргалка по комбинированию

Хочешь свою комбинацию? Бери секции из разных пресетов:

- **Цвета** — берёшь `--gray-*`, `--green*`, `--success-*` и т.д. из любого пресета.
- **Форма** — берёшь блок `--radius-*` отдельно. Например, цвета от «Ocean Depth», но радиусы от «Sunset Coral» — получится «деловая, но мягкая».
- **Темп** — `--transition-fast` / `--transition` управляют скоростью hover-эффектов.
- **Глубина** — все `--shadow-*` можно собрать из разных пресетов.

> ⚠️ При смене `--green` **обязательно** меняй и `--green-rgb` (а если меняешь `--gray-dark` — то и `--gray-dark-rgb`). Это RGB-компоненты для прозрачных теней — без них тени останутся прежнего цвета.
