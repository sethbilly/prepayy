<div class="col-md-4 m-t-md">
    <div class="panel panel-info">
        <div class="panel-body">
            <article class="card-typical">
                <div class="card-typical-section">
                    <div class="user-card-row">
                        <div class="tbl-row">
                            <div class="tbl-cell">
                                <p class="user-card-row-name">
                                    @if($application->loanApplicationStatus->status === 'Draft')
                                        <i class="font-icon font-icon-pencil"></i>&nbsp;
                                    @endif
                                    <a href="{{route($authUser->isBorrower() ? 'loan_applications.edit' : 'loan_applications.show', ['application' => $application])}}"
                                       style="font-size: 20px" data-toggle="tooltip" data-placement="top"
                                       title="{{ ucfirst($application->loanProduct->name) }}"
                                       class="text-success underline">
                                        <strong>
                                            {{ $application->loanProduct->name }}
                                        </strong>
                                    </a>
                                </p>
                                <p class="color-blue-grey-lighter">
                                    by {{ $application->loanProduct->institution->name }}
                                    @if (!$authUser->isBorrower())
                                        for {{ $application->user->getFullName() }}
                                    @endif
                                    <br/>{{$application->created_at->format('jS M, Y')}}
                                </p>
                                <br>
                                <p>
                                    <label class="label label-warning">
                                        {{ $application->loanApplicationStatus ? $application->loanApplicationStatus->display_status : 'N/A' }}
                                    </label>
                                    @if (!$authUser->isBorrower() && !empty($application->status_label))
                                        <br/><small style="font-size:12px">{{$application->status_label->level}}</small>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-typical-section">
                    <div class="card-typical-linked">
                        {{$currency}}&nbsp;{{isset($application->amount) ? number_format($application->amount, 2) : ''}}
                        at {{ number_format($application->loanProduct->interest_per_year, 2) }}%
                        for {{$application->tenure_in_years}}
                        {{isset($application->tenure_in_years) ? str_plural('year', $application->tenure_in_years) : ''}}
                    </div>
                </div>
                <div class="card-typical-section">
                    <div class="card-typical-linked">
                        @php
                            $monthlyPayable = !empty($application->monthly_payable) ? $currency . ' ' . number_format($application->monthly_payable, 2) : 'n/a';
                            $tenureInMonths= !empty($application->tenure_in_months) ? $application->tenure_in_months . ' months' : 'n/a';
                            $totalPayable = !empty($application->total_payable) ? $currency . ' ' . number_format($application->total_payable, 2) : 'n/a';
                        @endphp
                        Repay {{$monthlyPayable}} for {{$tenureInMonths}}<br/>({{$totalPayable}} in total)
                    </div>
                </div>
            </article>
        </div>
    </div>
</div>
