<?php
function what_time_is_it(float $angle): string {
    return gmdate("h:i", $angle * 2 * 60);
}
what_time_is_it("10:00");