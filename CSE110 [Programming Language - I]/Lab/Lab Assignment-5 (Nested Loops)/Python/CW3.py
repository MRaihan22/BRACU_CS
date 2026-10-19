start = int(input("Start: "))
end = int(input("End: "))
print("Armstrong numbers: ")
for num in range(start, end+1):
    temp = num
    digits = 0

    while temp != 0:
        temp //= 10
        digits += 1

    temp = num
    sum = 0

    while temp != 0:
        digit = temp % 10
        sum += digit ** digits
        temp //= 10

    if sum == num:
        print(num)