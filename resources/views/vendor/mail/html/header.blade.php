@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://e.top4top.io/p_3531x6fzb1.png" class="logo" alt="Smart Developer" >
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
