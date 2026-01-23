(function () {
  console.log("dashboard.js loaded");

  const backdrop = document.getElementById('txModalBackdrop');
  const closeBtn = document.getElementById('closeTxModal');
  const closeSecondary = document.getElementById('closeTxModalSecondary');
  const openBtn = document.getElementById('openAddTx');

  const form = document.getElementById('txForm');
  const title = document.getElementById('txModalTitle');
  const submitBtn = document.getElementById('txSubmitBtn');
  const txIdInput = document.getElementById('tx_id');

  const typeEl = document.getElementById('type');
  const dateEl = document.getElementById('occurred_on');
  const catEl = document.getElementById('category_id');
  const amountEl = document.getElementById('amount');
  const descEl = document.getElementById('description');

  if (!backdrop || !form || !title || !submitBtn || !txIdInput || !typeEl || !dateEl || !catEl || !amountEl || !descEl) {
    console.warn("Brak części elementów DOM (modal/form). Sprawdź ID w dashboard.php");
    return;
  }

  function openModal() {
    backdrop.classList.add('is-open');
    const first = backdrop.querySelector('select, input');
    if (first) first.focus();
  }

  function closeModal() {
    backdrop.classList.remove('is-open');
  }

  function setModeAdd() {
    title.textContent = 'Dodaj transakcję';
    submitBtn.textContent = 'Zapisz';
    form.action = '/transactions';
    txIdInput.value = '';

    typeEl.value = 'expense';
    amountEl.value = '';
    descEl.value = '';
    // dateEl zostaje (domyślnie ustawione w PHP)
  }

  function setModeEdit(data) {
    title.textContent = 'Edytuj transakcję';
    submitBtn.textContent = 'Zapisz zmiany';
    form.action = '/transactions/update';

    txIdInput.value = data.id;
    typeEl.value = data.type;
    catEl.value = data.categoryId;
    amountEl.value = data.amount;
    descEl.value = data.description || '';
    dateEl.value = data.occurredOn;
  }

  // Dodawanie
  openBtn?.addEventListener('click', () => {
    setModeAdd();
    openModal();
  });

  // Zamknięcia
  closeBtn?.addEventListener('click', closeModal);
  closeSecondary?.addEventListener('click', closeModal);

  backdrop.addEventListener('click', (e) => {
    if (e.target === backdrop) closeModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && backdrop.classList.contains('is-open')) {
      closeModal();
    }
  });

  // ✅ Delegacja eventów dla Edytuj i Usuń (działa zawsze)
  document.addEventListener('click', (e) => {
    const editBtn = e.target.closest('.js-edit');
    if (editBtn) {
      const data = {
        id: editBtn.dataset.id,
        type: editBtn.dataset.type,
        categoryId: editBtn.dataset.categoryId,
        amount: editBtn.dataset.amount,
        description: editBtn.dataset.description,
        occurredOn: editBtn.dataset.occurredOn,
      };

      console.log("Edit clicked:", data);

      setModeEdit(data);
      openModal();
      return;
    }
  });

  // Confirm dla usuwania
  document.addEventListener('submit', (e) => {
    const delForm = e.target.closest('.js-delete-form');
    if (delForm) {
      if (!confirm('Na pewno usunąć tę transakcję?')) {
        e.preventDefault();
      }
    }
  });
})();
