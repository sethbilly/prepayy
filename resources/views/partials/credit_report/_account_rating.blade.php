<div class="col-md-12">
    <div class="col-md-3">
        <label class="form-label">Highest Delinquency Rating</label>
        <p>
            {{ $report->delinquencyInformation->highestDelinquencyRating}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">No of Home Loan Accounts (Good)</label>
        <p>
            {{ $report->accountRating->noOfHomeLoanAccountsGood}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">No of Home Loan Accounts (Bad)</label>
        <p>
            {{ $report->accountRating->noOfHomeLoanAccountsBad}}
        </p>
    </div>

    <div class="col-md-3">
        <label class="form-label">No of Auto Loan Accounts (Good)</label>
        <p>
            {{ $report->accountRating->noOfAutoLoanAccountsGood}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">No of Auto Loan Accounts (Bad)</label>
        <p>
            {{ $report->accountRating->noOfAutoLoanAccountsBad}}
        </p>
    </div>

    <div class="col-md-3">
        <label class="form-label">No of Study Loan Accounts (Good)</label>
        <p>
            {{ $report->accountRating->noOfStudyLoanAccountsGood}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">No of Study Loan Accounts (Bad)</label>
        <p>
            {{ $report->accountRating->noOfStudyLoanAccountsBad}}
        </p>
    </div>

    <div class="col-md-3">
        <label class="form-label">No of Personal Loan Accounts (Good)</label>
        <p>
            {{ $report->accountRating->noOfPersonalLoanAccountsGood}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">No of Personal Loan Accounts (Bad)</label>
        <p>
            {{ $report->accountRating->noOfPersonalLoanAccountsBad}}
        </p>
    </div>

    <div class="col-md-3">
        <label class="form-label">No of Credit Card Accounts (Good)</label>
        <p>
            {{ $report->accountRating->noOfCreditCardAccountsGood}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">No of Credit Card Accounts (Bad)</label>
        <p>
            {{ $report->accountRating->noOfCreditCardAccountsBad}}
        </p>
    </div>

    <div class="col-md-3">
        <label class="form-label">No of Retail Loan Accounts (Good)</label>
        <p>
            {{ $report->accountRating->noOfRetailAccountsGood}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">No of Retail Loan Accounts (Bad)</label>
        <p>
            {{ $report->accountRating->noOfRetailAccountsBad}}
        </p>
    </div>

    <div class="col-md-3">
        <label class="form-label">No of Joint Loan Accounts (Good)</label>
        <p>
            {{ $report->accountRating->noOfJointLoanAccountsGood}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">No of Joint Loan Accounts (Bad)</label>
        <p>
            {{ $report->accountRating->noOfJointLoanAccountsBad}}
        </p>
    </div>

    <div class="col-md-3">
        <label class="form-label">No of Telecom Loan Accounts (Good)</label>
        <p>
            {{ $report->accountRating->noOfTelecomAccountsGood}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">No of Telecom Loan Accounts (Bad)</label>
        <p>
            {{ $report->accountRating->noOfTelecomAccountsBad}}
        </p>
    </div>

    <div class="col-md-3">
        <label class="form-label">No of Other Loan Accounts (Good)</label>
        <p>
            {{ $report->accountRating->noOfOtherAccountsGood}}
        </p>
    </div>
    <div class="col-md-3">
        <label class="form-label">No of Other Loan Accounts (Bad)</label>
        <p>
            {{ $report->accountRating->noOfOtherAccountsBad}}
        </p>
    </div>
</div>