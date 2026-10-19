num = int(input("Input: "))
temp = num
digits = 0
if temp == 0:
    digits = 1
else:
    while temp != 0:
        temp //= 10
        digits += 1

divisor = 1
for i in range(1, digits):
    divisor *= 10

while divisor >= 1:
        digits = num // divisor
        print(digits, end ="")
        if divisor > 1:
            print(", ", end="")
        num %= divisor
        divisor //= 10
print()