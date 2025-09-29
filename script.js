const loginForm = document.getElementById("loginForm");
if (loginForm) {
  loginForm.addEventListener("submit", async function (e) {
    e.preventDefault();
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    try {
      await firebase.auth().signInWithEmailAndPassword(email, password);
      window.location.href = "index.html";
    } catch (error) {
      // alert("The email or password is incorrect.");
      document.getElementById("message").style.color = "red";
      document.getElementById("message").textContent =
        "The email or password is incorrect.";
    }
  });
}

function togglePassword() {
  const passwordField = document.getElementById("password");
  passwordField.type = passwordField.type === "password" ? "text" : "password";
}

window.addEventListener("DOMContentLoaded", function () {
  const btn = document.getElementById("someElement");
  if (btn) {
    btn.addEventListener("click", function () {
      // do something
    });
  }
});

///////////////////////////////////////////////////////

const $ = (s, el = document) => el.querySelector(s);
const $all = (s, el = document) => [...el.querySelectorAll(s)];

const store = {
  key: "tour-packages",
  get() {
    return JSON.parse(localStorage.getItem(this.key) || "[]");
  },
  set(data) {
    localStorage.setItem(this.key, JSON.stringify(data));
  },
  nextId(list) {
    return list.reduce((m, r) => Math.max(m, r.id), 0) + 1;
  },
};

const state = { list: store.get(), filter: { q: "", from: "", to: "" } };

function renderTable() {
  const tbody = $("#tbody");
  tbody.innerHTML = "";
  let rows = [...state.list];
  if (state.filter.q)
    rows = rows.filter((r) =>
      r.title.toLowerCase().includes(state.filter.q.toLowerCase())
    );
  if (state.filter.from)
    rows = rows.filter((r) => !r.startDate || r.startDate >= state.filter.from);
  if (state.filter.to)
    rows = rows.filter((r) => !r.endDate || r.endDate <= state.filter.to);

  if (rows.length === 0) {
    $("#empty").hidden = false;
    return;
  } else $("#empty").hidden = true;

  for (const r of rows) {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${r.id}</td>
      <td><strong>${r.title}</strong><div>${r.note || ""}</div></td>
      <td>${r.daysNights}</td>
      <td>${r.route}</td>
      <td>${r.startDate} - ${r.endDate}</td>
      <td>
        <button class="btn secondary" data-edit="${r.id}">แก้ไข</button>
        <button class="btn danger" data-del="${r.id}">ลบ</button>
      </td>`;
    tbody.appendChild(tr);
  }
}

function addPriceRow(values = {}) {
  const tpl = $("#priceRowTpl").content.cloneNode(true);
  $(".vehicle", tpl).value = values.vehicleType || "5-7 Seater";
  $(".passengers", tpl).value = values.passengers || "";
  $(".price", tpl).value = values.price || "";
  $(".btnDelPrice", tpl).onclick = (e) => e.target.closest("tr").remove();
  $("#priceBody").appendChild(tpl);
}

function collectPrices() {
  return $all("#priceBody tr").map((tr) => ({
    vehicleType: $(".vehicle", tr).value,
    passengers: +$(".passengers", tr).value,
    price: +$(".price", tr).value,
  }));
}

function openCreate() {
  $("#tourForm").reset();
  $("#editingId").value = "";
  $("#priceBody").innerHTML = "";
  addPriceRow();
  addPriceRow({ vehicleType: "9 Seater" });
  $("#modal").showModal();
}

function openEdit(id) {
  const item = state.list.find((x) => x.id == id);
  if (!item) return;
  $("#editingId").value = id;
  $("#title").value = item.title;
  $("#daysNights").value = item.daysNights;
  $("#route").value = item.route;
  $("#startDate").value = item.startDate;
  $("#endDate").value = item.endDate;
  $("#note").value = item.note;
  $("#priceBody").innerHTML = "";
  item.prices.forEach((p) => addPriceRow(p));
  $("#modal").showModal();
}

function closeForm() {
  document.getElementById("formModal").style.display = "none";
  document.getElementById("tourForm").reset(); // รีเซ็ตค่าด้วย
}

$("#tourForm").onsubmit = (e) => {
  e.preventDefault();
  const id = +$("#editingId").value || store.nextId(state.list);
  const payload = {
    id,
    title: $("#title").value,
    daysNights: $("#daysNights").value,
    route: $("#route").value,
    startDate: $("#startDate").value,
    endDate: $("#endDate").value,
    note: $("#note").value,
    prices: collectPrices(),
  };
  const idx = state.list.findIndex((x) => x.id == id);
  if (idx >= 0) state.list[idx] = payload;
  else state.list.push(payload);
  store.set(state.list);
  $("#modal").close();
  renderTable();
};

$("#tbody").onclick = (e) => {
  if (e.target.dataset.edit) openEdit(+e.target.dataset.edit);
  if (e.target.dataset.del) {
    if (confirm("ลบแพ็กเกจนี้?")) {
      state.list = state.list.filter((x) => x.id != e.target.dataset.del);
      store.set(state.list);
      renderTable();
    }
  }
};

$("#btnAdd").onclick = openCreate;
$("#btnExport").onclick = () => {
  const dataStr =
    "data:text/json;charset=utf-8," +
    encodeURIComponent(JSON.stringify(state.list, null, 2));
  const a = document.createElement("a");
  a.href = dataStr;
  a.download = "tours.json";
  a.click();
};
$("#btnAddPrice").onclick = () => addPriceRow();

$("#q").oninput = (e) => {
  state.filter.q = e.target.value;
  renderTable();
};
$("#from").onchange = (e) => {
  state.filter.from = e.target.value;
  renderTable();
};
$("#to").onchange = (e) => {
  state.filter.to = e.target.value;
  renderTable();
};
$("#btnReset").onclick = () => {
  state.filter = { q: "", from: "", to: "" };
  $("#q").value = "";
  $("#from").value = "";
  $("#to").value = "";
  renderTable();
};

if (state.list.length === 0) {
  state.list = [
    {
      id: 1,
      title: "5 National Parks",
      daysNights: "6 Days 5 Nights",
      route:
        "Chengdu → Siguniang → Bipenggou → Dagu Glacier → Jiuzhaigou → Huanglong",
      startDate: "2025-07-01",
      endDate: "2025-09-30",
      note: "Note: 01/07/2025 - 30/09/2025",
      prices: [{ vehicleType: "5-7 Seater", passengers: 2, price: 31000 }],
    },
  ];
  store.set(state.list);
}
renderTable();
