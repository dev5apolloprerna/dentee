<table>
    <thead>
        <tr>
            <!--<th>Patient ID</th>-->
            <!--<th>Clinic ID</th>-->
            <!--<th>Branch ID</th>-->
            <th>Sr. No.</th>
            <th>Case No</th>
            <th>Name</th>
            <th>Mobile No</th>
            <th>Email</th>
            <th>Date of Birth</th>
            <th>Gender</th>
            <th>Address</th>
            <th>Group Name</th>
            <!--<th>Created At</th>-->
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; ?>
        @foreach($patients as $patient)
        <tr>
            <!--<td>{{ $patient->patient_id }}</td>
            <td>{{ $patient->clinic_id }}</td>
            <td>{{ $patient->branch_id }}</td>-->
            <td>{{ $i }}</td>
            <td>{{ $patient->case_no }}</td>
            <td>{{ $patient->name_prefix }} {{ $patient->name }}</td>
            <td>{{ $patient->mobile_no }}</td>
            <td>{{ $patient->email }}</td>
            <td>{{ $patient->date_of_birth }}</td>
            <td>{{ $patient->gender }}</td>
            <td>{{ $patient->address }}</td>
            <td>{{ $patient->group_name }}</td>
            <!--<td>{{ $patient->created_at }}</td>-->
        </tr>
        <?php $i++; ?>
        @endforeach
    </tbody>
</table>