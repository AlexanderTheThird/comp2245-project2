// Filter buttons on the ticket list: fetch JSON, rebuild the table body, no page reload.
const body = document.getElementById('ticket-body');
const countText = document.getElementById('ticket-count');
const errorBox = document.getElementById('ticket-error');
const buttons = document.querySelectorAll('.filter-btn');

// Helper: make an element with text (textContent, never innerHTML)
function cell(text, className) {
  const td = document.createElement('td');
  td.textContent = text;
  if (className) td.className = className;
  return td;
}

// Helper: make a badge element with text (textContent, never innerHTML)
function badge(value) {
  const span = document.createElement('span');
  // "In progress" -> "in-progress", same as slug() in PHP
  span.className = 'badge badge-' + value.toLowerCase().replace(/\s+/g, '-');
  span.textContent = value;
  return span;
}

// Build a table row for a ticket object
function buildRow(ticket) {
  const tr = document.createElement('tr');
  tr.appendChild(cell(ticket.id));

  const titleCell = document.createElement('td');
  const link = document.createElement('a');
  link.href = '/ticket.php?id=' + encodeURIComponent(ticket.id);
  link.textContent = ticket.title;
  titleCell.appendChild(link);
  tr.appendChild(titleCell);

  const priorityCell = document.createElement('td');
  priorityCell.appendChild(badge(ticket.priority));
  tr.appendChild(priorityCell);

  const statusCell = document.createElement('td');
  statusCell.appendChild(badge(ticket.status));
  tr.appendChild(statusCell);

  tr.appendChild(cell(ticket.created_by_name));
  // The API sends null when nobody is assigned
  if (ticket.assigned_to_name === null) {
    tr.appendChild(cell('Nobody', 'muted'));
  } else {
    tr.appendChild(cell(ticket.assigned_to_name));
  }
  tr.appendChild(cell(ticket.created_at));
  return tr;
}

// Load tickets from the API and rebuild the table body
async function loadTickets(filter) {
  errorBox.hidden = true;
  try {
    const data = await apiRequest('/api/tickets.php?filter=' + encodeURIComponent(filter));
    body.replaceChildren(...data.tickets.map(buildRow));
    countText.textContent = 'Showing ' + data.count + (data.count === 1 ? ' ticket' : ' tickets');
    buttons.forEach((btn) => {
      btn.classList.toggle('is-active', btn.dataset.filter === filter);
    });
  } catch (error) {
    errorBox.textContent = error.message;
    errorBox.hidden = false;
  }
}

// Initial load: use the filter from the URL query string, defaulting to 'all'
buttons.forEach((btn) => {
  btn.addEventListener('click', () => loadTickets(btn.dataset.filter));
});
