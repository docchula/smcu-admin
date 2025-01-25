<?php

namespace App;

enum ProjectClosureStatus: int {
    case NOT_SUBMITTED = 0;
    case SUBMITTED = 1;
    case SUBMITTED_NO_VERIFICATION = 3; // Submitted but not enough verification in the time limit
    case REVIEWING = 5;
    case REVIEWING_NO_CLOSURE_DOC = 6; // Reviewing but no closure document uploaded
    case APPROVED = 10;
    case REJECTED = -1;
    case REJECTED_AND_RESUBMIT = -2; // Rejected but allow resubmission within the time limit
    case REJECTED_RESUBMIT_EXPIRED = -3; // Rejected and resubmission time limit has expired
}
