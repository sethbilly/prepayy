<?php

use App\Entities\LoanApplicationStatus;

class LoanApplicationStatusTest extends TestCase
{
    public function dataProvider()
    {
        return [
            ['draft'],
            ['employer_pending'],
            ['employer_approved'],
            ['employer_declined'],
            ['employer_information_requested'],
            ['partner_pending'],
            ['partner_approved'],
            ['partner_declined'],
            ['partner_disbursed'],
            ['partner_information_requested']
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param string $status
     */
    public function test_can_get_different_statuses(string $status)
    {
        $rec = LoanApplicationStatus::getStatus($status);

        $displayName = config('cloudloan.' . $status);

        $this->assertInstanceOf(LoanApplicationStatus::class, $rec);
        $this->assertEquals($displayName, $rec->display_status);
    }

    public function test_cannot_add_random_status()
    {
        $status = 'My Improvised Status';

        $rec = LoanApplicationStatus::getStatus($status);

        $this->assertNull($rec);
    }

    public function test_is_draft_and_is_pending_employer_statuses()
    {
        $rec = LoanApplicationStatus::getStatus(LoanApplicationStatus::STATUS['DRAFT_SAVED']);
        $rec2 = LoanApplicationStatus::getStatus(LoanApplicationStatus::STATUS['EMPLOYER_PENDING']);

        $this->assertTrue($rec->isDraft());
        $this->assertFalse($rec->isPendingEmployerApproval());

        $this->assertFalse($rec2->isDraft());
        $this->assertTrue($rec2->isPendingEmployerApproval());
    }
}
