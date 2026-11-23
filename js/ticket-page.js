// M5: AJAX for the ticket page (add note, change status, assign to me).
// Uses apiRequest() from common.js, which adds the CSRF header and throws
// an Error with a readable message when the server sends {"error": ...}.
(() => {
  const page = document.getElementById('ticket-page');
  if (!page) {
    return;
  }
  const ticketId = Number(page.dataset.ticketId);

  const noteForm = document.getElementById('note-form');
  const noteBody = document.getElementById('note-body');
  const noteMessage = document.getElementById('note-message');
  const noteList = document.getElementById('note-list');
  const statusSelect = document.getElementById('status-select');
  const statusBadge = document.getElementById('ticket-status');
  const assignedCell = document.getElementById('ticket-assigned');
  const updatedCell = document.getElementById('ticket-updated');
  const assignButton = document.getElementById('assign-me');
  const ticketMessage = document.getElementById('ticket-message');

  // Same idea as slug() in PHP: "In progress" -> "in-progress" (for CSS classes).
  const slug = (text) => text.toLowerCase().replace(/\s+/g, '-');

  const showNoteMessage = (text) => {
    noteMessage.textContent = text;
    noteMessage.hidden = text === '';
  };

  // Updates the status badge, "Assigned to" and "Last updated" from the server's data.
  const applyTicket = (ticket) => {
    statusBadge.textContent = ticket.status;
    statusBadge.className = 'badge badge-' + slug(ticket.status);
    statusSelect.value = ticket.status;
    assignedCell.textContent = ticket.assigned_to_name === null ? 'Nobody' : ticket.assigned_to_name;
    updatedCell.textContent = ticket.updated_at;
  };

  // --- Add a note ---
  noteForm.addEventListener('submit', async (event) => {
    event.preventDefault(); // stop the normal page-reloading submit
    const text = noteBody.value.trim();
    if (text === '') {
      showNoteMessage('Write a note before adding it.');
      return;
    }
    showNoteMessage('');
    const submitButton = noteForm.querySelector('button[type="submit"]');
    submitButton.disabled = true; // stops double-clicks adding the note twice
    try {
      const data = await apiRequest('/api/notes.php', {
        method: 'POST',
        body: { ticket_id: ticketId, body: text },
      });

      // Build the note with createElement + textContent (never innerHTML).
      const note = document.createElement('article');
      note.className = 'note';
      const meta = document.createElement('p');
      meta.className = 'note-meta';
      meta.textContent = data.note.author_name + ' \u00b7 ' + data.note.created_at;
      const body = document.createElement('p');
      body.className = 'note-body';
      body.textContent = data.note.body;
      note.append(meta, body);

      const empty = document.getElementById('no-notes');
      if (empty) {
        empty.remove();
      }
      noteList.append(note); // bottom of the list = newest last
      noteBody.value = '';
    } catch (error) {
      showNoteMessage(error.message);
    } finally {
      submitButton.disabled = false;
    }
  });

  // --- Change status ---
  let lastStatus = statusSelect.value;
  statusSelect.addEventListener('change', async () => {
    ticketMessage.textContent = 'Saving...';
    try {
      const data = await apiRequest('/api/ticket-update.php', {
        method: 'POST',
        body: { ticket_id: ticketId, action: 'status', status: statusSelect.value },
      });
      applyTicket(data.ticket);
      lastStatus = data.ticket.status;
      ticketMessage.textContent = 'Saved.';
    } catch (error) {
      statusSelect.value = lastStatus; // put the dropdown back: it didn't save
      ticketMessage.textContent = error.message;
    }
  });

  // --- Assign to me ---
  if (assignButton) {
    assignButton.addEventListener('click', async () => {
      assignButton.disabled = true;
      ticketMessage.textContent = 'Saving...';
      try {
        const data = await apiRequest('/api/ticket-update.php', {
          method: 'POST',
          body: { ticket_id: ticketId, action: 'assign_me' },
        });
        applyTicket(data.ticket);
        assignButton.hidden = true; // already assigned to me, so hide it
        ticketMessage.textContent = 'Saved.';
      } catch (error) {
        assignButton.disabled = false;
        ticketMessage.textContent = error.message;
      }
    });
  }
})();
