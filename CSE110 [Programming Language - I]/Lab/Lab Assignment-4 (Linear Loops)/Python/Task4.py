num = int(input("Input: "))
count = 0
if num == 0:
    count = 1
else:
    if num < 0:
        num = -num
    while num != 0:
        num //= 10
        count += 1
print("Total digits =", count)