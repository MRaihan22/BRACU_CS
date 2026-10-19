num = int(input("Input: "))
# Prime Checking
divisor_count = 0
for i in range(1, num+1):
    if num % i == 0:
        divisor_count += 1
if divisor_count == 2:
    print(num, " is a Prime Number")
else:
    print(num, " is not a Prime Number")

# Perfect checking
sum = 0
for i in range(1, num):
    if num % i == 0:
        sum += i
if sum == num:
    print(num, " is a Perfect Number")
else:
    print(num, " is not a Perfect Number")