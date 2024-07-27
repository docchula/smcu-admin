<?php

namespace App;

enum ProjectClosureStatus: int {
    case NOT_SUBMITTED = 0;
    case SUBMITTED = 1;
    case REVIEWING = 5;
    case APPROVED = 10;
    case REJECTED = -1;
}
