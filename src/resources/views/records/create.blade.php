@extends('layouts.app')

@section('content')
<h1>Registrar Conformidade - {{ $category->name }}</h1>
<form method="POST" action="{{ route('records.store', $category) }}">
    @csrf
    <input type="hidden" name="month" value="{{ $today->month }}">
    <table class="table table-bordered">
        <tr>
            <th>Item \ Dia</th>
            @foreach($days as $day)
                <th>{{ $day }}</th>
            @endforeach
        </tr>
        @foreach($items as $item)
        <tr>
            <td>{{ $item->description }}</td>
            @foreach($days as $day)
            <td>
                <select name="records[{{ $item->id }}][{{ $day }}]" class="form-select form-select-sm">
                    <option value="">-</option>
                    <option value="C">C</option>
                    <option value="NC">NC</option>
                </select>
            </td>
            @endforeach
        </tr>
        @endforeach
    </table>
    <button class="btn btn-success">Salvar</button>
</form>
@endsection
