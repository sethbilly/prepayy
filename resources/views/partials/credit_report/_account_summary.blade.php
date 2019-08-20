<div class="col-md-12">
    <div class="col-md-3">
        <label class="form-label">Total Monthly Installment</label>
        <p>
            {{$currency}}&nbsp;{{ $report->creditAccountSummary->totalMonthlyInstalment}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">Total Outstanding Debt</label>
        <p>
            {{$currency}}&nbsp;{{ $report->creditAccountSummary->totalOutstandingDebt}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">Total Accounts in Arrears</label>
        <p>
            {{ $report->creditAccountSummary->totalAccountArrears}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">Total Amount in Arrears</label>
        <p>
            {{$currency}}&nbsp;{{ $report->creditAccountSummary->amountInArrears}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">Total Accounts in Good Condition</label>
        <p>
            {{ $report->creditAccountSummary->totalAccountsInGoodCondition}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">Total No. of Judgement</label>
        <p>
            {{ $report->creditAccountSummary->totalNumberOfJudgement}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">Total Judgement Amount</label>
        <p>
            {{$currency}}&nbsp;{{ $report->creditAccountSummary->totalJudgementAmount}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">Total No. of Dishonoured Cheques</label>
        <p>
            {{ $report->creditAccountSummary->totalNumberOfDishonouredCheques}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">Total Dishonoured Cheques Amount</label>
        <p>
            {{$currency}}&nbsp;{{ $report->creditAccountSummary->totalDishonouredAmount}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">Last Bounced Cheques Date</label>
        <p>
            {{ $report->creditAccountSummary->lastBouncedChequesDate}}
        </p>
    </div>
</div>