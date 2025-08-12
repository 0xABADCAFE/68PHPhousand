.ori_byte:
	ori.b #$bb,$8(a0,d0.w)
	rts

.ori_word:
	ori.w #$cccc,$10(a0,d0.w)
	rts

.ori_long:
	ori.l #$abadcafe,$18(a0,d0.w)
	rts

