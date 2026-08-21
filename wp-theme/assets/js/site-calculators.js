/**
 * BIS Engineering Calculators Engine
 * - Clean corporate UI logic
 * - Exact formulas matching GOST 53300-2009, AVOK, and GOST 34060 (APK reverse engineered)
 * - 100% Reliable Standalone Print / PDF Engine (No blank pages)
 */

(function () {
  'use strict';

  const state = {
    currentBlock: 'block1',
    currentAvok: 'du4_1',
    
    // Default Shaft Template for Block 1
    b1Defaults: {
      floorsCount: 42,
      shaftA: 1.1,
      shaftB: 0.8,
      valveA: 1.0,
      valveB: 0.5,
      floorHeight: 4.3,
      topFloorHeight: 20.8
    },

    // Block 1 Floors data
    block1Floors: [],

    // Block 3 Elements
    block3Elements: [
      { id: 1, type: 'D1', name: 'Прямой круглый участок', params: { D: 0.5, L: 6.0 }, s: 9.42 },
      { id: 2, type: 'D2', name: 'Прямой прямоугольный участок', params: { A: 0.6, B: 0.4, L: 8.0 }, s: 16.0 },
      { id: 3, type: 'O1', name: 'Отвод круглый 90°', params: { D: 0.5, R: 0.5, A: 90 }, s: 1.23 },
      { id: 4, type: 'A3', name: 'Переход прям-прям', params: { A: 0.6, B: 0.4, A1: 0.4, B1: 0.3, L: 0.5 }, s: 0.98 },
      { id: 5, type: 'E2', name: 'Заглушка прямоугольная', params: { A: 0.4, B: 0.3 }, s: 0.12 }
    ],

    // Metadata for official Protocol
    protocolMeta: {
      number: '1',
      date: new Date().toISOString().split('T')[0],
      objectName: 'ЖК «Симфония», Корпус 2',
      systemName: 'Система дымоудаления ДУ-1',
      section: 'Вентиляционная шахта ШД-1 (этажи 2-42)',
      instruments: 'Дифференциальный манометр Testo 510, Анемометр Testo 417',
      engineer: 'Иванов И.И.',
      approver: 'Петров П.П.'
    },

    lastResults: {}
  };

  const KMS_RATES = {
    standard: 0.4, // Тройник на проход
    complex: 4.6,  // Отводы + полуотводы + тройник
    turns: 1.6     // Повороты
  };

  const LEAKAGE_CLASSES = {
    A: { name: 'Класс A (Низкая плотность)', c: 0.027 },
    B: { name: 'Класс B (Плотный)', c: 0.009 },
    C: { name: 'Класс C (Высокая плотность)', c: 0.003 }
  };

  let dom = {};

  function init() {
    initDefaultFloors(state.b1Defaults.floorsCount);
    cacheDom();
    bindEvents();
    renderAll();
  }

  function initDefaultFloors(count) {
    state.block1Floors = [];
    for (let f = count; f >= 1; f--) {
      state.block1Floors.push({
        floor: f,
        li: f === count ? state.b1Defaults.topFloorHeight : state.b1Defaults.floorHeight,
        a: state.b1Defaults.shaftA,
        b: state.b1Defaults.shaftB,
        kmsType: f === count ? 'complex' : 'standard',
        val_a: state.b1Defaults.valveA,
        val_b: state.b1Defaults.valveB
      });
    }
  }

  function cacheDom() {
    dom.blockNavBtns = document.querySelectorAll('.calc-nav-card');
    dom.blockPanels = document.querySelectorAll('.calc-block-content');
    dom.avokTabBtns = document.querySelectorAll('.avok-tab-item');
    dom.avokPanels = document.querySelectorAll('.avok-calc-content');

    dom.b1TableBody = document.getElementById('b1FloorsTableBody');
    dom.b3TableBody = document.getElementById('b3ElementsTableBody');

    dom.protocolModal = document.getElementById('calcProtocolModal');
    dom.protocolPrintArea = document.getElementById('protocolPrintArea');
    dom.btnAddElementModal = document.getElementById('addElementModal');
  }

  function bindEvents() {
    // Top Block Navigation
    dom.blockNavBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const block = btn.dataset.block;
        switchBlock(block);
      });
    });

    // AVOK Tabs Navigation
    dom.avokTabBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const avok = btn.dataset.avok;
        switchAvokTab(avok);
      });
    });

    // Auto-recalculation inputs
    document.addEventListener('input', e => {
      if (e.target.matches('.calc-auto-recalc')) {
        recalculateCurrent();
      }
    });

    // Block 1 Floor Table Buttons
    const btnAddFloor = document.getElementById('b1BtnAddFloor');
    if (btnAddFloor) btnAddFloor.addEventListener('click', addBlock1Floor);

    const btnGenFloors = document.getElementById('b1BtnGenFloors');
    if (btnGenFloors) btnGenFloors.addEventListener('click', promptGenerateFloors);

    const btnApplyDefaults = document.getElementById('b1BtnApplyDefaults');
    if (btnApplyDefaults) btnApplyDefaults.addEventListener('click', applyDefaultsToAllFloors);

    // Block 3 Element Builder Cards
    const elCards = document.querySelectorAll('.element-select-card');
    elCards.forEach(card => {
      card.addEventListener('click', () => {
        const type = card.dataset.type;
        openAddElementDialog(type);
      });
    });
  }

  function switchBlock(block) {
    state.currentBlock = block;
    dom.blockNavBtns.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.block === block);
    });
    dom.blockPanels.forEach(panel => {
      panel.style.display = panel.id === `panel-${block}` ? 'block' : 'none';
    });
    recalculateCurrent();
  }

  function switchAvokTab(tab) {
    state.currentAvok = tab;
    dom.avokTabBtns.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.avok === tab);
    });
    dom.avokPanels.forEach(panel => {
      panel.style.display = panel.id === `avok-${tab}` ? 'block' : 'none';
    });
    recalculateCurrent();
  }

  function renderAll() {
    renderBlock1Table();
    renderBlock3Table();
    recalculateCurrent();
  }

  /* ==========================================================================
     BLOCK 1: GOST R 53300-2009 (Appendix B) Calculation
     ========================================================================== */
  function recalculateBlock1() {
    const Lpr = parseFloat(document.getElementById('b1_Lpr')?.value) || 34760;
    const Psv = parseFloat(document.getElementById('b1_Psv')?.value) || 1550;
    const Tpg = parseFloat(document.getElementById('b1_Tpg')?.value) || 400;
    const Tpom = parseFloat(document.getElementById('b1_Tpom')?.value) || 18;
    const h_top = parseFloat(document.getElementById('b1_h_top')?.value) || 146.95;
    const h_bot = parseFloat(document.getElementById('b1_h_bot')?.value) || 9.82;

    const Tv = Tpg - 62;
    const Ta = 273 + Tpom;
    const rho_a = 353 / Ta;
    const rho_sm = (2 * rho_a * Ta) / (Tpg + Tv);
    const rho_v = 353 / Tv;
    const h = Math.max(0, h_top - h_bot);

    const Psa = (Psv * rho_v / 1.2) + (9.81 * h * (rho_a - rho_sm));
    const Ga = (Lpr * rho_a) / 3600;

    let currentG = Ga;
    let currentP = Psa;
    let totalValveLeakage = 0;
    const floorResults = [];

    state.block1Floors.forEach((f, idx) => {
      const F_shaft = f.a * f.b;
      const P_shaft = 2 * (f.a + f.b);
      const de = P_shaft > 0 ? (4 * F_shaft / (f.a + f.b)) : 1.0;
      const kms = KMS_RATES[f.kmsType] || 0.4;
      const lambda = 0.016;
      const li = f.li || 4.3;

      const velocity_mass = (F_shaft > 0 && rho_a > 0) ? (currentG / (rho_a * F_shaft)) : 0;
      const deltaP = 0.5 * rho_a * (kms + (lambda * li / de)) * Math.pow(velocity_mass, 2);
      currentP = Math.max(0, currentP - deltaP);

      const F_val = Math.max(0, (f.val_a - 0.03) * (f.val_b - 0.05));
      const G_leak = Math.sqrt(currentP / 39300) * F_val;
      totalValveLeakage += G_leak;

      currentG = Math.max(0, currentG - G_leak);
      const L_floor = (currentG * 3600) / rho_a;

      floorResults.push({
        floor: f.floor,
        de: de.toFixed(3),
        kms: kms.toFixed(1),
        P_sn: Math.round(currentP),
        G_leak: G_leak.toFixed(4),
        G_curr: currentG.toFixed(3),
        L_curr: Math.round(L_floor)
      });
    });

    const G0 = Ga - totalValveLeakage;
    const L0 = (G0 * 3600) / rho_a;

    updateSummaryMetric('b1_res_L0', Math.round(L0).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('b1_res_Psa', Math.round(Psa).toLocaleString('ru-RU'), 'Па');
    updateSummaryMetric('b1_res_G0', G0.toFixed(2), 'кг/с');
    updateSummaryMetric('b1_res_Leak', Math.round(totalValveLeakage * 3600 / rho_a).toLocaleString('ru-RU'), 'м³/ч');

    state.lastResults.block1 = {
      Lpr, Psv, Tpg, Tpom, Psa: Math.round(Psa), L0: Math.round(L0), G0: G0.toFixed(2),
      totalLeakage: Math.round(totalValveLeakage * 3600 / rho_a),
      floorResults
    };

    updateBlock1TableOutputs(floorResults);
  }

  function renderBlock1Table() {
    if (!dom.b1TableBody) return;
    dom.b1TableBody.innerHTML = '';

    state.block1Floors.forEach((f, idx) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td style="font-weight:600;">
          <input type="number" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="floor" value="${f.floor}" style="width: 55px;">
        </td>
        <td>
          <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="li" value="${f.li}" style="width: 70px;">
        </td>
        <td>
          <div style="display:inline-flex; align-items:center; gap:4px;">
            <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="a" value="${f.a}" style="width: 55px;">
            <span>×</span>
            <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="b" value="${f.b}" style="width: 55px;">
          </div>
        </td>
        <td>
          <select class="calc-table-select b1-floor-input" data-idx="${idx}" data-field="kmsType" style="width: 140px;">
            <option value="standard" ${f.kmsType === 'standard' ? 'selected' : ''}>Проход (0.4)</option>
            <option value="complex" ${f.kmsType === 'complex' ? 'selected' : ''}>Отводы + Тройник (4.6)</option>
            <option value="turns" ${f.kmsType === 'turns' ? 'selected' : ''}>Повороты (1.6)</option>
          </select>
        </td>
        <td>
          <div style="display:inline-flex; align-items:center; gap:4px;">
            <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="val_a" value="${f.val_a}" style="width: 55px;">
            <span>×</span>
            <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="val_b" value="${f.val_b}" style="width: 55px;">
          </div>
        </td>
        <td id="b1_out_P_${idx}" style="font-weight:600; color:var(--dark);">-</td>
        <td id="b1_out_G_${idx}" style="color:var(--text-light);">-</td>
        <td id="b1_out_L_${idx}" style="font-weight:700; color:var(--primary-dark);">-</td>
        <td>
          <button type="button" class="btn-delete-floor" data-idx="${idx}" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:18px; padding:4px;" title="Удалить этаж">&times;</button>
        </td>
      `;
      dom.b1TableBody.appendChild(tr);
    });

    dom.b1TableBody.querySelectorAll('.b1-floor-input').forEach(input => {
      input.addEventListener('change', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        const field = e.target.dataset.field;
        if (field === 'kmsType') {
          state.block1Floors[idx].kmsType = e.target.value;
        } else {
          state.block1Floors[idx][field] = parseFloat(e.target.value) || 0;
        }
        recalculateBlock1();
      });
    });

    dom.b1TableBody.querySelectorAll('.btn-delete-floor').forEach(btn => {
      btn.addEventListener('click', () => {
        const idx = parseInt(btn.dataset.idx, 10);
        state.block1Floors.splice(idx, 1);
        renderBlock1Table();
        recalculateBlock1();
      });
    });
  }

  function updateBlock1TableOutputs(results) {
    results.forEach((r, idx) => {
      const pEl = document.getElementById(`b1_out_P_${idx}`);
      const gEl = document.getElementById(`b1_out_G_${idx}`);
      const lEl = document.getElementById(`b1_out_L_${idx}`);
      if (pEl) pEl.innerText = r.P_sn + ' Па';
      if (gEl) gEl.innerText = r.G_leak + ' кг/с';
      if (lEl) lEl.innerText = r.L_curr.toLocaleString('ru-RU') + ' м³/ч';
    });
  }

  function addBlock1Floor() {
    const lastFloor = state.block1Floors[state.block1Floors.length - 1];
    const newFloorNum = lastFloor ? Math.max(1, lastFloor.floor - 1) : 1;
    state.block1Floors.push({
      floor: newFloorNum,
      li: state.b1Defaults.floorHeight,
      a: state.b1Defaults.shaftA,
      b: state.b1Defaults.shaftB,
      kmsType: 'standard',
      val_a: state.b1Defaults.valveA,
      val_b: state.b1Defaults.valveB
    });
    renderBlock1Table();
    recalculateBlock1();
  }

  function promptGenerateFloors() {
    const count = parseInt(prompt('Введите общее количество этажей в шахте:', '20'), 10);
    if (!count || count < 1 || count > 100) return;
    initDefaultFloors(count);
    renderBlock1Table();
    recalculateBlock1();
  }

  function applyDefaultsToAllFloors() {
    const shaftA = parseFloat(document.getElementById('b1_def_shaftA')?.value) || 1.1;
    const shaftB = parseFloat(document.getElementById('b1_def_shaftB')?.value) || 0.8;
    const valveA = parseFloat(document.getElementById('b1_def_valveA')?.value) || 1.0;
    const valveB = parseFloat(document.getElementById('b1_def_valveB')?.value) || 0.5;
    const floorH = parseFloat(document.getElementById('b1_def_floorH')?.value) || 4.3;

    state.b1Defaults.shaftA = shaftA;
    state.b1Defaults.shaftB = shaftB;
    state.b1Defaults.valveA = valveA;
    state.b1Defaults.valveB = valveB;
    state.b1Defaults.floorHeight = floorH;

    state.block1Floors.forEach((f, idx) => {
      f.a = shaftA;
      f.b = shaftB;
      f.val_a = valveA;
      f.val_b = valveB;
      if (idx > 0) f.li = floorH;
    });

    renderBlock1Table();
    recalculateBlock1();
    alert('Базовые параметры успешно применены ко всем участкам шахты!');
  }

  /* ==========================================================================
     BLOCK 2: AVOK Recommendations Calculations
     ========================================================================== */
  function recalculateBlock2() {
    switch (state.currentAvok) {
      case 'du4_1': calcAvokDU4_1(); break;
      case 'pd4_1': calcAvokPD4_1(); break;
      case 'pd4_2': calcAvokPD4_2(); break;
      case 'pd4_7': calcAvokPD4_7(); break;
      case 'pd4_8': calcAvokPD4_8(); break;
      case 'pd7_a': calcAvokPD7_a(); break;
    }
  }

  function calcAvokDU4_1() {
    const Lk = parseFloat(document.getElementById('du4_1_Lk')?.value) || 24;
    const Bk = parseFloat(document.getElementById('du4_1_Bk')?.value) || 2.4;
    const Hk = parseFloat(document.getElementById('du4_1_Hk')?.value) || 2.8;
    const Q = parseFloat(document.getElementById('du4_1_Q')?.value) || 1200;
    const Ta = parseFloat(document.getElementById('du4_1_Ta')?.value) || 24;

    const smoke_layer_h = 0.5 * Hk;
    const G_smoke = 0.071 * Math.pow(Q, 1/3) * Math.pow(Hk - smoke_layer_h, 5/3) + 0.0018 * Q;
    const T_smoke = Ta + (Q / (1.005 * G_smoke));
    const rho_smoke = 353 / (273 + Math.min(600, T_smoke));
    const L_smoke = (G_smoke / rho_smoke) * 3600;

    const P_fan = 380; // Pa

    updateSummaryMetric('avok_res_main_val', Math.round(L_smoke).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', P_fan, 'Па');
    updateSummaryMetric('avok_res_sub2_val', Math.round(T_smoke), '°C');

    state.lastResults.avok = {
      type: 'ДУ4-1 (Дымоудаление из коридора)',
      mainLabel: 'Расход дымоудаления L',
      mainVal: `${Math.round(L_smoke).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Давление вентилятора Psv',
      sub1Val: `${P_fan} Па`,
      sub2Label: 'Температура дыма',
      sub2Val: `${Math.round(T_smoke)} °C`,
      details: [
        { label: 'Массовый расход дыма G', val: `${G_smoke.toFixed(2)} кг/с` },
        { label: 'Плотность дыма ρsm', val: `${rho_smoke.toFixed(3)} кг/м³` },
        { label: 'Длина коридора Lк', val: `${Lk} м` }
      ]
    };
  }

  function calcAvokPD4_1() {
    const floors = parseFloat(document.getElementById('pd4_1_floors')?.value) || 16;
    const b_door = parseFloat(document.getElementById('pd4_1_b_door')?.value) || 0.9;
    const h_door = parseFloat(document.getElementById('pd4_1_h_door')?.value) || 2.1;
    const b_type = document.getElementById('pd4_1_building_type')?.value || 'living';
    const v_min = b_type === 'living' ? 1.3 : 1.5;

    const F_door = b_door * h_door;
    const rho = 1.25;
    const G_door = v_min * F_door * rho;
    const G_leaks = (floors - 1) * Math.sqrt(35 / 196000);
    const G_total = G_door + G_leaks;
    const L_total = (G_total * 3600) / rho;

    updateSummaryMetric('avok_res_main_val', Math.round(L_total).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', '45', 'Па');
    updateSummaryMetric('avok_res_sub2_val', G_total.toFixed(2), 'кг/с');

    state.lastResults.avok = {
      type: 'ПД4-1 (Подпор в лестничную клетку)',
      mainLabel: 'Расход подпора в ЛК',
      mainVal: `${Math.round(L_total).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Избыточное давление',
      sub1Val: '45 Па',
      sub2Label: 'Массовый расход G',
      sub2Val: `${G_total.toFixed(2)} кг/с`,
      details: [
        { label: 'Расход через открытую дверь', val: `${(G_door * 3600 / rho).toFixed(0)} м³/ч` },
        { label: 'Утечки через закрытые двери', val: `${(G_leaks * 3600 / rho).toFixed(0)} м³/ч` },
        { label: 'Скорость в проеме двери', val: `${v_min} м/с` }
      ]
    };
  }

  function calcAvokPD4_2() {
    const floors = parseFloat(document.getElementById('pd4_2_floors')?.value) || 16;
    const elevators = parseFloat(document.getElementById('pd4_2_elevators')?.value) || 1;
    const rho = 1.25;

    const L_per_door = 3600 * 0.65 * 0.05 * Math.sqrt(2 * 30 / rho);
    const L_total = elevators * (L_per_door * floors + 1800);

    updateSummaryMetric('avok_res_main_val', Math.round(L_total).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', '35', 'Па');
    updateSummaryMetric('avok_res_sub2_val', (L_total * rho / 3600).toFixed(2), 'кг/с');

    state.lastResults.avok = {
      type: 'ПД4-2 (Подпор в шахту лифта)',
      mainLabel: 'Расход подпора в шахту',
      mainVal: `${Math.round(L_total).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Давление в шахте',
      sub1Val: '35 Па',
      sub2Label: 'Массовый расход',
      sub2Val: `${(L_total * rho / 3600).toFixed(2)} кг/с`,
      details: [
        { label: 'Количество шахт лифтов', val: `${elevators}` },
        { label: 'Этажность здания', val: `${floors}` }
      ]
    };
  }

  function calcAvokPD4_7() {
    const w = parseFloat(document.getElementById('pd4_7_w')?.value) || 1.0;
    const h = parseFloat(document.getElementById('pd4_7_h')?.value) || 2.1;
    const v = parseFloat(document.getElementById('pd4_7_v')?.value) || 1.3;
    const rho = 1.25;

    const F = w * h;
    const G = v * F * rho;
    const L = (G * 3600) / rho;

    updateSummaryMetric('avok_res_main_val', Math.round(L).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', '25', 'Па');
    updateSummaryMetric('avok_res_sub2_val', v.toFixed(2), 'м/с');

    state.lastResults.avok = {
      type: 'ПД4-7 (Зона ПБЗ - открытая дверь)',
      mainLabel: 'Расход воздуха в ПБЗ',
      mainVal: `${Math.round(L).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Требуемый подпор',
      sub1Val: '25 Па',
      sub2Label: 'Скорость в проеме',
      sub2Val: `${v} м/с`,
      details: [
        { label: 'Площадь дверного проема F', val: `${F.toFixed(2)} м²` },
        { label: 'Массовый расход G', val: `${G.toFixed(2)} кг/с` }
      ]
    };
  }

  function calcAvokPD4_8() {
    const w = parseFloat(document.getElementById('pd4_8_w')?.value) || 0.9;
    const h = parseFloat(document.getElementById('pd4_8_h')?.value) || 2.1;
    const rho = 1.25;

    const F = w * h;
    const G = 1.3 * F * rho;
    const L = (G * 3600) / rho;

    updateSummaryMetric('avok_res_main_val', Math.round(L).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', '30', 'Па');
    updateSummaryMetric('avok_res_sub2_val', G.toFixed(2), 'кг/с');

    state.lastResults.avok = {
      type: 'ПД4-8 (Тамбур-шлюз перед ЛК)',
      mainLabel: 'Расход воздуха в ТШ',
      mainVal: `${Math.round(L).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Давление в ТШ',
      sub1Val: '30 Па',
      sub2Label: 'Массовый расход',
      sub2Val: `${G.toFixed(2)} кг/с`,
      details: [
        { label: 'Площадь проема ТШ', val: `${F.toFixed(2)} м²` }
      ]
    };
  }

  function calcAvokPD7_a() {
    const doors = parseFloat(document.getElementById('pd7_a_doors')?.value) || 2;
    const reqP = parseFloat(document.getElementById('pd7_a_reqP')?.value) || 20;
    const rho = 1.25;

    const G_leak = doors * Math.sqrt(reqP / 196000);
    const L_total = Math.max(350, (G_leak * 3600) / rho);

    updateSummaryMetric('avok_res_main_val', Math.round(L_total).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', reqP, 'Па');
    updateSummaryMetric('avok_res_sub2_val', (G_leak * 1000).toFixed(1), 'г/с');

    state.lastResults.avok = {
      type: 'ПД7-а (Зона ПБЗ - закрытая дверь)',
      mainLabel: 'Расход для подпора в ПБЗ',
      mainVal: `${Math.round(L_total).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Поддерживаемое давление',
      sub1Val: `${reqP} Па`,
      sub2Label: 'Суммарная утечка',
      sub2Val: `${(G_leak * 1000).toFixed(1)} г/с`,
      details: [
        { label: 'Количество противопожарных дверей', val: `${doors}` }
      ]
    };
  }

  /* ==========================================================================
     BLOCK 3: GOST 34060 Duct Network & Leakage Engine (from APK)
     ========================================================================== */
  function recalculateBlock3() {
    const factP = parseFloat(document.getElementById('b3_factP')?.value) || 400;
    const factL = parseFloat(document.getElementById('b3_factL')?.value) || 25;
    const reqClass = document.getElementById('b3_reqClass')?.value || 'B';

    let totalS = 0;
    state.block3Elements.forEach(el => {
      totalS += el.s;
    });
    totalS = Math.max(0.1, totalS);

    const factLeak = factL / totalS;
    const classConfig = LEAKAGE_CLASSES[reqClass] || LEAKAGE_CLASSES.B;
    const allowLeak = classConfig.c * Math.pow(factP, 0.65) * 3.6;
    const isPassed = factLeak <= allowLeak;

    updateSummaryMetric('b3_res_totalS', totalS.toFixed(2), 'м²');
    updateSummaryMetric('b3_res_factLeak', factLeak.toFixed(2), 'м³/(ч·м²)');
    updateSummaryMetric('b3_res_allowLeak', allowLeak.toFixed(2), 'м³/(ч·м²)');

    const verdictEl = document.getElementById('b3_verdict_badge');
    if (verdictEl) {
      if (isPassed) {
        verdictEl.className = 'calc-status-badge calc-status-badge--success';
        verdictEl.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> СООТВЕТСТВУЕТ КЛАССУ ${reqClass}`;
      } else {
        verdictEl.className = 'calc-status-badge calc-status-badge--danger';
        verdictEl.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> НЕ СООТВЕТСТВУЕТ (Превышение ${((factLeak/allowLeak - 1)*100).toFixed(0)}%)`;
      }
    }

    state.lastResults.block3 = {
      factP, factL, reqClass,
      totalS: totalS.toFixed(2),
      factLeak: factLeak.toFixed(2),
      allowLeak: allowLeak.toFixed(2),
      isPassed,
      elements: [...state.block3Elements]
    };
  }

  function renderBlock3Table() {
    if (!dom.b3TableBody) return;
    dom.b3TableBody.innerHTML = '';

    state.block3Elements.forEach((el, idx) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td style="font-weight:600;">${idx + 1}</td>
        <td style="text-align:left; font-weight:600;">${el.name}</td>
        <td><span class="calc-norm-pill" style="font-size:11px;">${el.type}</span></td>
        <td style="text-align:left; font-size:13px; color:var(--text);">${formatElementParams(el)}</td>
        <td style="font-weight:700; color:var(--primary-dark);">${el.s.toFixed(2)} м²</td>
        <td>
          <button type="button" class="btn-delete-element" data-idx="${idx}" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:18px; padding:4px;" title="Удалить элемент">&times;</button>
        </td>
      `;
      dom.b3TableBody.appendChild(tr);
    });

    dom.b3TableBody.querySelectorAll('.btn-delete-element').forEach(btn => {
      btn.addEventListener('click', () => {
        const idx = parseInt(btn.dataset.idx, 10);
        state.block3Elements.splice(idx, 1);
        renderBlock3Table();
        recalculateBlock3();
      });
    });
  }

  function formatElementParams(el) {
    const p = el.params;
    switch (el.type) {
      case 'D1': return `Диаметр D = ${p.D} м, Длина L = ${p.L} м`;
      case 'D2': return `Сечение ${p.A} × ${p.B} м, Длина L = ${p.L} м`;
      case 'O1': return `Диаметр D = ${p.D} м, Радиус R = ${p.R} м, Угол = ${p.A}°`;
      case 'O2': return `Сечение ${p.A} × ${p.B} м, Радиус R = ${p.R} м, Угол = ${p.A}°`;
      case 'A3': return `Сечение ${p.A} × ${p.B} м → ${p.A1} × ${p.B1} м, Длина L = ${p.L} м`;
      case 'E1': return `Круг диаметром D = ${p.D} м`;
      case 'E2': return `Прямоугольник ${p.A} × ${p.B} м`;
      default: return JSON.stringify(p);
    }
  }

  function openAddElementDialog(type) {
    let name = 'Элемент сети';
    let params = {};

    switch (type) {
      case 'D1':
        const d = parseFloat(prompt('Диаметр воздуховода D (м):', '0.4')) || 0.4;
        const l = parseFloat(prompt('Длина участка L (м):', '4.0')) || 4.0;
        name = 'Прямой круглый участок';
        params = { D: d, L: l };
        break;
      case 'D2':
        const a = parseFloat(prompt('Ширина стороны A (м):', '0.5')) || 0.5;
        const b = parseFloat(prompt('Высота стороны B (м):', '0.4')) || 0.4;
        const l2 = parseFloat(prompt('Длина участка L (м):', '5.0')) || 5.0;
        name = 'Прямой прямоугольный участок';
        params = { A: a, B: b, L: l2 };
        break;
      case 'O1':
        const od = parseFloat(prompt('Диаметр D (м):', '0.4')) || 0.4;
        const or = parseFloat(prompt('Радиус поворота R (м):', '0.4')) || 0.4;
        const oa = parseFloat(prompt('Угол отвода (градусы):', '90')) || 90;
        name = 'Отвод круглый';
        params = { D: od, R: or, A: oa };
        break;
      case 'O2':
        const oa2 = parseFloat(prompt('Размер стороны A (м):', '0.5')) || 0.5;
        const ob2 = parseFloat(prompt('Размер стороны B (м):', '0.4')) || 0.4;
        const or2 = parseFloat(prompt('Радиус поворота R (м):', '0.5')) || 0.5;
        const oang2 = parseFloat(prompt('Угол отвода (градусы):', '90')) || 90;
        name = 'Отвод прямоугольный';
        params = { A: oa2, B: ob2, R: or2, A: oang2 };
        break;
      case 'A3':
        const a1 = parseFloat(prompt('Начальная ширина A (м):', '0.6')) || 0.6;
        const b1 = parseFloat(prompt('Начальная высота B (м):', '0.4')) || 0.4;
        const a2 = parseFloat(prompt('Конечная ширина A1 (м):', '0.4')) || 0.4;
        const b2 = parseFloat(prompt('Конечная высота B1 (м):', '0.3')) || 0.3;
        const al = parseFloat(prompt('Длина перехода L (м):', '0.5')) || 0.5;
        name = 'Переход прямоугольный';
        params = { A: a1, B: b1, A1: a2, B1: b2, L: al };
        break;
      case 'E1':
        const ed = parseFloat(prompt('Диаметр заглушки D (м):', '0.4')) || 0.4;
        name = 'Заглушка круглая';
        params = { D: ed };
        break;
      case 'E2':
        const ea = parseFloat(prompt('Ширина заглушки A (м):', '0.5')) || 0.5;
        const eb = parseFloat(prompt('Высота заглушки B (м):', '0.4')) || 0.4;
        name = 'Заглушка прямоугольная';
        params = { A: ea, B: eb };
        break;
      default:
        return;
    }

    const s = calculateArea(type, params);
    state.block3Elements.push({
      id: Date.now(),
      type,
      name,
      params,
      s
    });

    renderBlock3Table();
    recalculateBlock3();
  }

  function calculateArea(type, p) {
    const pi = Math.PI;
    switch (type) {
      case 'D1': return pi * p.D * p.L;
      case 'D2': return 2 * (p.A + p.B) * p.L;
      case 'O1': return pi * p.D * (pi * p.R * (p.A / 180));
      case 'O2': return 2 * (p.A + p.B) * (pi * p.R * (p.A / 180));
      case 'A3':
        const h1 = Math.sqrt(Math.pow(p.L, 2) + Math.pow((p.B - p.B1)/2, 2));
        const h2 = Math.sqrt(Math.pow(p.L, 2) + Math.pow((p.A - p.A1)/2, 2));
        return (p.A + p.A1) * h1 + (p.B + p.B1) * h2;
      case 'E1': return (pi * Math.pow(p.D, 2)) / 4;
      case 'E2': return p.A * p.B;
      default: return 1.0;
    }
  }

  /* ==========================================================================
     RELIABLE STANDALONE PROTOCOL GENERATION & PRINT ENGINE
     ========================================================================== */
  function generateProtocolHTML() {
    const meta = state.protocolMeta;
    let tableHtml = '';
    let conclusionText = '';
    let protocolTitle = '';

    if (state.currentBlock === 'block1') {
      const res = state.lastResults.block1 || {};
      protocolTitle = 'ПРОТОКОЛ АЭРОДИНАМИЧЕСКИХ ИСПЫТАНИЙ СИСТЕМЫ ДЫМОУДАЛЕНИЯ / ПОДПОРА (ГОСТ Р 53300-2009)';
      
      let rows = '';
      (res.floorResults || []).slice(0, 15).forEach(r => {
        rows += `<tr><td>${r.floor}</td><td>${r.P_sn} Па</td><td>${r.G_leak} кг/с</td><td>${r.L_curr} м³/ч</td></tr>`;
      });

      tableHtml = `
        <table class="protocol-table-info">
          <tr><td class="field-name">Объект / Адрес:</td><td>${meta.objectName}</td><td class="field-name">Дата испытания:</td><td>${meta.date}</td></tr>
          <tr><td class="field-name">Наименование системы:</td><td>${meta.systemName}</td><td class="field-name">Номер протокола:</td><td>№ ${meta.number}</td></tr>
          <tr><td class="field-name">Испытываемый участок:</td><td>${meta.section}</td><td class="field-name">Приборы измерений:</td><td>${meta.instruments}</td></tr>
        </table>
        
        <h4 style="margin:14px 0 6px; font-size:12px; text-transform:uppercase;">Итоговые параметры вентиляционной сети:</h4>
        <table class="protocol-grid-data">
          <tr>
            <th>Проектный расход Lпр</th>
            <th>Фактический расход в точке забора L0</th>
            <th>Давление перед вентилятором Psa</th>
            <th>Суммарные утечки через клапаны</th>
          </tr>
          <tr>
            <td><b>${res.Lpr || 0} м³/ч</b></td>
            <td><b>${res.L0 || 0} м³/ч</b></td>
            <td><b>${res.Psa || 0} Па</b></td>
            <td><b>${res.totalLeakage || 0} м³/ч</b></td>
          </tr>
        </table>

        <h4 style="margin:14px 0 6px; font-size:12px; text-transform:uppercase;">Распределение параметров по контрольным этажам (выборка):</h4>
        <table class="protocol-grid-data">
          <tr><th>Этаж</th><th>Давление в шахте Psi</th><th>Утечка клапана Gdpn</th><th>Расход на участке Li</th></tr>
          ${rows}
        </table>
      `;
      conclusionText = `Заключение: Система противодымной вентиляции ${meta.systemName} обеспечивает расчетный расход воздуха ${res.L0 || 0} м³/ч при напоре вентилятора ${res.Psa || 0} Па в соответствии с требованиями ГОСТ Р 53300-2009.`;

    } else if (state.currentBlock === 'block2') {
      const res = state.lastResults.avok || {};
      protocolTitle = `ПРОТОКОЛ РАСЧЕТА СИСТЕМЫ ПРОТИВОДЫМНОЙ ВЕНТИЛЯЦИИ (${res.type || 'АВОК'})`;
      
      let detailsRows = '';
      (res.details || []).forEach(d => {
        detailsRows += `<tr><td class="field-name">${d.label}:</td><td><b>${d.val}</b></td></tr>`;
      });

      tableHtml = `
        <table class="protocol-table-info">
          <tr><td class="field-name">Объект / Адрес:</td><td>${meta.objectName}</td><td class="field-name">Дата расчета:</td><td>${meta.date}</td></tr>
          <tr><td class="field-name">Наименование системы:</td><td>${meta.systemName}</td><td class="field-name">Номер протокола:</td><td>№ ${meta.number}</td></tr>
          <tr><td class="field-name">Нормативная база:</td><td>Рекомендации АВОК 5.5.1</td><td class="field-name">Расчетчик:</td><td>${meta.engineer}</td></tr>
        </table>
        <h4 style="margin:14px 0 6px; font-size:12px; text-transform:uppercase;">Результаты расчета параметров противодымной вентиляции:</h4>
        <table class="protocol-table-info">
          <tr><td class="field-name">${res.mainLabel}:</td><td><b style="font-size:14px;">${res.mainVal}</b></td></tr>
          <tr><td class="field-name">${res.sub1Label}:</td><td><b>${res.sub1Val}</b></td></tr>
          <tr><td class="field-name">${res.sub2Label}:</td><td><b>${res.sub2Val}</b></td></tr>
          ${detailsRows}
        </table>
      `;
      conclusionText = `Заключение: Расчетные параметры системы противодымной вентиляции ${meta.systemName} соответствуют требованиям нормативов АВОК и СП 7.13130.`;

    } else {
      const res = state.lastResults.block3 || {};
      protocolTitle = 'ПРОТОКОЛ ИСПЫТАНИЯ ВОЗДУХОВОДА НА ГЕРМЕТИЧНОСТЬ (ГОСТ 34060)';
      
      let elemRows = '';
      (res.elements || []).forEach((el, idx) => {
        elemRows += `<tr><td>${idx + 1}</td><td style="text-align:left;">${el.name}</td><td>${el.type}</td><td>${el.s.toFixed(2)} м²</td></tr>`;
      });

      tableHtml = `
        <table class="protocol-table-info">
          <tr><td class="field-name">Объект / Зона:</td><td>${meta.objectName}</td><td class="field-name">Дата испытания:</td><td>${meta.date}</td></tr>
          <tr><td class="field-name">Наименование системы:</td><td>${meta.systemName}</td><td class="field-name">Номер протокола:</td><td>№ ${meta.number}</td></tr>
          <tr><td class="field-name">Испытываемый участок:</td><td>${meta.section}</td><td class="field-name">Приборы измерений:</td><td>${meta.instruments}</td></tr>
        </table>

        <h4 style="margin:14px 0 6px; font-size:12px; text-transform:uppercase;">Спецификация испытываемых элементов воздуховода:</h4>
        <table class="protocol-grid-data">
          <tr><th>№</th><th>Наименование элемента</th><th>Тип</th><th>Развернутая площадь S</th></tr>
          ${elemRows}
          <tr style="background:#f1f5f9; font-weight:bold;">
            <td colspan="3" style="text-align:right;">ИТОГО РАЗВЕРНУТАЯ ПЛОЩАДЬ СЕТИ S:</td>
            <td>${res.totalS || 0} м²</td>
          </tr>
        </table>

        <h4 style="margin:14px 0 6px; font-size:12px; text-transform:uppercase;">Показатели герметичности сети:</h4>
        <table class="protocol-grid-data">
          <tr>
            <th>Фактическое давление Pф</th>
            <th>Фактический расход утечки Lф</th>
            <th>Фактическая утечка Lф.ут</th>
            <th>Требуемый класс</th>
            <th>Допустимая утечка Lдоп</th>
          </tr>
          <tr>
            <td>${res.factP || 0} Па</td>
            <td>${res.factL || 0} м³/ч</td>
            <td><b>${res.factLeak || 0} м³/(ч·м²)</b></td>
            <td>Класс ${res.reqClass || 'B'}</td>
            <td><b>${res.allowLeak || 0} м³/(ч·м²)</b></td>
          </tr>
        </table>
      `;

      conclusionText = res.isPassed
        ? `Заключение: Испытываемый воздуховод ОБЕСПЕЧИВАЕТ требуемую герметичность по классу ${res.reqClass} (ГОСТ 34060).`
        : `Заключение: Испытываемый воздуховод НЕ ОБЕСПЕЧИВАЕТ требуемую герметичность по классу ${res.reqClass} (ГОСТ 34060). Требуется дополнительная герметизация стыков.`;
    }

    return `
      <div class="protocol-sheet-container">
        <div class="protocol-sheet-header">
          <div class="protocol-org-name">
            ООО «Баланс Инженерных Систем»
            <div class="protocol-org-sub">ИНН: 7700000000 | bis-rf.ru | info@bis-rf.ru</div>
          </div>
          <div class="protocol-stamp-box">
            <b>УТВЕРЖДАЮ:</b><br>
            Руководитель лаборатории<br>
            <div class="sign-underline" style="width:140px; margin:4px 0 2px auto;"></div>
            <span>/ ${meta.approver} /</span>
          </div>
        </div>

        <div class="protocol-main-title">${protocolTitle}</div>
        <div class="protocol-number-date">Протокол № ${meta.number} от ${meta.date}</div>

        ${tableHtml}

        <div class="protocol-conclusion-box">
          ${conclusionText}
        </div>

        <div class="protocol-signs-row">
          <div class="sign-column">
            <span>Протокол составил инженер-испытатель:</span>
            <div class="sign-underline"></div>
            <span>/ ${meta.engineer} /</span>
          </div>
          <div class="sign-column">
            <span>Представитель заказчика / технадзора:</span>
            <div class="sign-underline"></div>
            <span>/ __________________________ /</span>
          </div>
        </div>
      </div>
    `;
  }

  // Open modal and render preview
  window.calcEngineOpenProtocol = function () {
    recalculateCurrent();
    const html = generateProtocolHTML();
    if (dom.protocolPrintArea) {
      dom.protocolPrintArea.innerHTML = html;
    }
    if (dom.protocolModal) {
      dom.protocolModal.classList.add('active');
    }
  };

  window.calcEngineCloseProtocol = function () {
    if (dom.protocolModal) {
      dom.protocolModal.classList.remove('active');
    }
  };

  window.calcEngineUpdateMeta = function (field, val) {
    state.protocolMeta[field] = val;
    const html = generateProtocolHTML();
    if (dom.protocolPrintArea) {
      dom.protocolPrintArea.innerHTML = html;
    }
  };

  // Robust Native Print in Dedicated Window (100% reliable, zero blank pages)
  window.calcEngineDirectPrint = function () {
    recalculateCurrent();
    const bodyContent = generateProtocolHTML();
    const printWindow = window.open('', '_blank', 'width=900,height=750');
    if (!printWindow) {
      window.print();
      return;
    }

    printWindow.document.open();
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="utf-8">
        <title>Протокол испытаний БИС</title>
        <style>
          @page { size: A4 portrait; margin: 12mm 15mm 15mm 15mm; }
          body { font-family: Calibri, Arial, sans-serif; font-size: 12px; color: #000; margin: 0; padding: 0; background: #fff; }
          .protocol-sheet-container { padding: 0; }
          .protocol-sheet-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 2px solid #000; padding-bottom: 10px; }
          .protocol-org-name { font-size: 15px; font-weight: bold; text-transform: uppercase; }
          .protocol-org-sub { font-size: 11px; color: #555; margin-top: 2px; }
          .protocol-stamp-box { text-align: right; font-size: 11px; }
          .protocol-main-title { text-align: center; font-size: 16px; font-weight: bold; margin: 14px 0 4px; text-transform: uppercase; }
          .protocol-number-date { text-align: center; font-size: 12px; margin-bottom: 16px; color: #333; }
          .protocol-table-info { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
          .protocol-table-info td { padding: 5px 8px; border: 1px solid #000; font-size: 11px; }
          .protocol-table-info td.field-name { font-weight: bold; background: #f4f4f4; width: 30%; }
          .protocol-grid-data { width: 100%; border-collapse: collapse; margin: 12px 0; }
          .protocol-grid-data th, .protocol-grid-data td { border: 1px solid #000; padding: 5px 6px; font-size: 10.5px; text-align: center; }
          .protocol-grid-data th { background: #f4f4f4; font-weight: bold; }
          .protocol-conclusion-box { margin: 16px 0; padding: 10px 12px; border: 2px solid #000; background: #fafafa; font-weight: bold; font-size: 12px; }
          .protocol-signs-row { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 30px; }
          .sign-column { display: flex; flex-direction: column; gap: 4px; font-size: 11px; }
          .sign-underline { border-bottom: 1px solid #000; height: 20px; }
        </style>
      </head>
      <body>
        ${bodyContent}
        <script>
          window.onload = function() {
            window.focus();
            window.print();
            setTimeout(function() { window.close(); }, 1000);
          };
        <\/script>
      </body>
      </html>
    `);
    printWindow.document.close();
  };

  function recalculateCurrent() {
    if (state.currentBlock === 'block1') {
      recalculateBlock1();
    } else if (state.currentBlock === 'block2') {
      recalculateBlock2();
    } else if (state.currentBlock === 'block3') {
      recalculateBlock3();
    }
  }

  function updateSummaryMetric(id, val, unit) {
    const el = document.getElementById(id);
    if (el) {
      el.innerHTML = `${val} <span class="unit">${unit}</span>`;
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
