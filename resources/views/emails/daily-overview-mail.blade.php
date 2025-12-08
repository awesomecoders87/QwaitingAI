<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Daily Overview - Qwaiting.com</title>
    <style>
      body {
        font-family: "Helvetica Neue", sans-serif;
        background-color: #f9fafb;
        color: #111827;
        margin: 0;
        padding: 0;
      }
      .container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }
      .heading {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1f2937;
      }
      .section-title {
        font-weight: 600;
        margin-top: 1.5rem;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 0.25rem;
      }
      .summary-table {
        width: 100%;
        margin-top: 0.5rem;
        border-collapse: collapse;
      }
      .summary-table td {
        padding: 0.5rem 0;
        border-bottom: 1px solid #f3f4f6;
      }
      .footer {
        text-align: center;
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 2rem;
      }
      /* Department Table */
      #department-summary {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed; /* key for layout */
      }
      #department-summary th,
      #department-summary td {
        padding: 8px;
        border: 1px solid #ddd;
        word-wrap: break-word; /* break long words */
        white-space: normal; /* allow wrapping */
      }
      #department-summary thead tr {
        background-color: #f4f4f4;
        border-bottom: 1px solid #ccc;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <p class="text-gray-600 text-sm">📅 {{ $date }}</p>
      <h1 class="heading">Daily Overview Summary – Qwaiting.com</h1>

      <div class="section">
        <h2 class="section-title">🔄 System Activity</h2>
        <table class="summary-table">
          <tr>
            <td>Total Appointments Booked:</td>
            <td>{{ $bookings['booked'] }}</td>
          </tr>
          <tr>
            <td>Total Appointments Completed:</td>
            <td>{{ $bookings['completed'] }}</td>
          </tr>
          <tr>
            <td>Total Appointments Confirmed:</td>
            <td>{{ $bookings['confirmed'] }}</td>
          </tr>
          <tr>
            <td>Total Appointments Pending:</td>
            <td>{{ $bookings['pending'] }}</td>
          </tr>
          <tr>
            <td>Total Appointments Cancelled:</td>
            <td>{{ $bookings['cancelled'] }}</td>
          </tr>
          <tr>
            <td>No-Shows Today:</td>
            <td>{{ $bookings['total'] }}</td>
          </tr>
        </table>
      </div>
      <div class="section">
        <h2 class="section-title">🔄 Ticket Genearte</h2>
        <table class="summary-table">
          <tr>
            <td>Total Ticket Generate:</td>
            <td>{{ $tickets['total'] }}</td>
          </tr>
          <tr>
            <td>Total Ticket Served:</td>
            <td>{{ $tickets['completed'] }}</td>
          </tr>
          <tr>
            <td>Total Ticket Not Served:</td>
            <td>{{ $tickets['pending'] }}</td>
          </tr>
          <tr>
            <td>Total Ticket Cancelled:</td>
            <td>{{ $tickets['cancelled'] }}</td>
          </tr>
          <tr>
            <td>Total Ticket Generate from Booking:</td>
            <td>{{ $tickets['ticket_generate_from_booking'] }}</td>
          </tr>
        </table>
      </div>
      <div class="section">
        <h2 class="section-title">🔄 Revenue</h2>
        <table class="summary-table">
          <tr>
            <td>Total Transactions:</td>
            <td>{{ $revenue['total_transactions'] }}</td>
          </tr>
          <tr>
            <td>Total Revenue:</td>
            <td>{{ $revenue['total_revenue'] }}</td>
          </tr>
        </table>
      </div>

      <div class="section">
        <h2 class="section-title">🏢 Department Summary</h2>
        <div class="table-wrapper">
          <table
            id="department-summary"
            cellpadding="0"
            cellspacing="0"
            border="0"
          >
            <thead>
              <tr>
                <th align="left">Department</th>
                <th align="left">Bookings</th>
                <th align="left">Completed</th>
                <th align="left">Pending</th>
                <th align="left">Cancelled</th>
                <th align="left">Revenue</th>
              </tr>
            </thead>
            <tbody>
              @foreach($departmentsSummary as $department)
              <tr>
                <td><strong>{{ $department['category']['name'] }}</strong></td>
                <td>{{ $department['total_calls'] }}</td>
                <td>{{ $department['served_calls'] }}</td>
                <td>{{ $department['pending_calls'] }}</td>
                <td>{{ $department['cancel_calls'] }}</td>
                <td>{{ $department['total_revenue'] }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <!-- Staff Performance -->
      <div class="section">
        <h2 class="section-title">👨‍💼 Staff Performance</h2>
        @if(!empty($staffReport))
        <table
          style="
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 14px;
          "
        >
          <thead style="background-color: #f3f4f6">
            <tr>
              <th
                style="
                  padding: 12px;
                  text-align: left;
                  border-bottom: 2px solid #d1d5db;
                "
              >
                Name
              </th>
              <th
                style="
                  padding: 12px;
                  text-align: right;
                  border-bottom: 2px solid #d1d5db;
                "
              >
                Tickets Served
              </th>
              <th
                style="
                  padding: 12px;
                  text-align: right;
                  border-bottom: 2px solid #d1d5db;
                "
              >
                Total Service Time
              </th>
              <th
                style="
                  padding: 12px;
                  text-align: right;
                  border-bottom: 2px solid #d1d5db;
                "
              >
                Avg. Service Time
              </th>
            </tr>
          </thead>
          <tbody>
            @foreach($staffReport as $staff)
            <tr style="border-bottom: 1px solid #e5e7eb">
              <td style="padding: 10px"></td>
              <td style="padding: 10px; text-align: right"></td>
              <td style="padding: 10px; text-align: right"></td>
              <td
                style="padding: 10px; text-align: right; color: {% if staff.avg_service_time > 10 %}#ef4444{% else %}#10b981{% endif %};"
              >

              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
       @else
        <p style="color: #6b7280; font-size: 0.875rem">
          No staff performance data available.
        </p>
       @endif
      </div>

      <!-- Staff Feedback Table -->
      <div class="section" style="margin-top: 2rem">
        <h2 class="section-title">💬 Staff Feedback</h2>
        @if(!empty($staffFeedback))
        <table
          style="
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 14px;
          "
        >
          <thead style="background-color: #fef3c7">
            <tr>
              <th
                style="
                  padding: 8px;
                  text-align: left;
                  border-bottom: 2px solid #fcd34d;
                "
              >
                Staff
              </th>
              <th
                style="
                  padding: 8px;
                  text-align: left;
                  border-bottom: 2px solid #fcd34d;
                "
              >
                Question
              </th>
              <th
                style="
                  padding: 8px;
                  text-align: left;
                  border-bottom: 2px solid #fcd34d;
                "
              >
                Feedback
              </th>
              <th
                style="
                  padding: 8px;
                  text-align: right;
                  border-bottom: 2px solid #fcd34d;
                "
              >
                Rating
              </th>
            </tr>
          </thead>
          <tbody>
           @foreach($staffFeedback as $feedback)
            <tr style="border-bottom: 1px solid #fde68a">
              <td style="padding: 8px">{{ feedback.staff_name }}</td>
              <td style="padding: 8px">{{ $feedback['question'] }}</td>
              <td style="padding: 8px; font-style: italic; color: #374151">
                “{{ $feedback['comment'] }}”
              </td>
             <td
                style="padding: 8px; text-align: right; color: {{
                    $feedback['rating'] >= 4 ? '#10b981' :
                    ($feedback['rating'] >= 2 ? '#f59e0b' :
                    ($feedback['rating'] >= 1 ? '#ef4444' : '#000'))
                }};"
              >

                {{ $feedback['rating'] }} / 5
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
       @else
        <p style="color: #6b7280; font-size: 0.875rem">
          No feedback received for today.
        </p>
        @endif
      </div>

      <div class="section">
        <h2 class="section-title">📝 Notes</h2>
        <p class="text-gray-700 text-sm"></p>
      </div>

      <div class="footer">
        Sent by Qwaiting System Bot –
        <a href="https://qwaiting.com" class="text-blue-500">qwaiting.com</a>
      </div>
    </div>
  </body>
</html>