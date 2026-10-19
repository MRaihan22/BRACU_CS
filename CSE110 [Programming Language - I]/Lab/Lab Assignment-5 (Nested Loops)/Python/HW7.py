start = int(input("Starting Number: "))
end = int(input("Ending Number: "))
divisor = int(input("Divisor: "))
print("Output: ")
for num in range(start, end+1):
    temp = num
    prod = 1
    while temp != 0:
        digit = temp % 10
        prod *= digit
        temp //= 10
    if prod % divisor == 0:
        print(prod, end=" ")