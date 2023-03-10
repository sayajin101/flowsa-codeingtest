@component('mail::message')
# Upcoming Deadlines

The following deadlines are looming!

@component('mail::table')
| Title                   | Deadline                   |
| ----------------------- |---------------------------:|
@foreach ($todos as $todo)
| {{ $todo->title }}      | {{ $todo->deadline }}      |
@endforeach
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
